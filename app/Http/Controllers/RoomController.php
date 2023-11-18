<?php
//
//namespace App\Http\Controllers;
//
//use App\Models\Room;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Hash;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Response;
//
//class RoomController extends Controller
//{
//    /**
//     * Display a listing of the resource.
//     *
//     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
//     * @return Response
//     */
//    public function index(Request $request)
//    {
//    //$rooms = Room::all();
//    return Room::where('type', $request->get('type'))->latest()->paginate(20);
//    //return response()->json($rooms, 200);
//
//    }
//
//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param Request $request
//     * @return JsonResponse
//    **/
//    public function store(Request $request)
//    {
//        $name = $request->get('name');
//        $capacity = $request->get('capacity');
//        $type = $request->get('type');
//        $user_id = auth()->user()->id;
//        $user_id_room = $request->get('user_id');
//        $name_room = $request->get('name');
//        if (Hash::check($user_id_room,$name_room)) {
//            return new \HttpException("У вас уже есть комната", 400);
//        }
//        else {return Room::create([
//            'name' => $name,
//            'capacity' => $capacity,
//            'type' => $type,
//            'user_id' => $user_id]);
//        }
//
//    }
//
//    public function store(Request $request)
//    {
//        $name = $request->get('name');
//        $capacity=$request->get('capacity');
//        $type=$request->get('type');
//        $user_id=auth()->user()->id;
//        $existingRoom = Room::where('user_id', $user_id)->exists(); //используется для выполнения запроса к базе данных с целью проверки наличия комнаты для конкретного пользователя
//
//
//        if ($existingRoom) {
//            return response()->json(['message' => 'У вас уже есть комната'], 400);
//        }
//        else {
//            $newRoom = Room::create([
//                'name' => $name,
//                'capacity' => $capacity,
//                'type' => $type,
//                'user_id' => $user_id,
//                'is_active' => true,
//            ]);
//
//            return response()->json($newRoom, 201); // Возвращаем созданную комнату с кодом 201 (Created)
//        }
//
//    }
//
//    /**
//     * Display the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function show($id)
//    {
//        return Room::find($id);
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function update(Request $request, $id)
//    {
//        //
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function destroy($id)
//    {
//        //$room = Room::find($id);
//        //$room->delete();
//       return Room::where('id', $id)->delete();
//    }
//    public function enter(Room $room, Request $request){
//        $user = auth() -> user();
//        if ($room->capacity === $user -> rooms->count()) {
//            return response()->json(['message' => "fail, room is full"]);
//        }
//
//        $user -> rooms() -> attach($room->id);
//        //detach
//
//        return response()->json(['message' => 'success']);
//    }
//
//    public function leave(Room $room, Request $request){
//        $user = auth() -> user();
//        $user -> rooms() -> detach($room->id);
//        return response()->json(['message' => 'successfully leave']);
//    }
//}
namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return Room::where('type', $request->get('type'))->latest()->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $name = $request->get('name');
        $capacity = $request->get('capacity');
        $type = $request->get('type');
        $user = auth()->user();

        $existingRoom = Room::where('user_id', $user->id)->first();
        if ($existingRoom) {
            return response()->json(['message' => 'У вас уже есть комната', 'room' => $existingRoom->id], 400);
        }

        $newRoom = Room::create([
            'name' => $name,
            'capacity' => $capacity,
            'type' => $type,
            'user_id' => $user->id
        ]);
        $user->rooms()->attach($newRoom->id);

        return response()->json(['message' => 'Комната создана успешно', 'room' => $newRoom], 201);
    }
    /**public function store(Request $request)
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(Room $room): Response|Room
    {
        return $room;
    }

    public function leave(Room $room): JsonResponse
    {
        auth()->user()->rooms()->detach($room->id);
        if (blank($room->users)) {
            $room->delete();
        }
        return response()->json(["message" => "Success"]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        /**   public function update(Request $request, $id)
         * Remove the specified resource from storage.
         *
         * @param int $id
         * @return Response
         */
    }
    public function destroy($id)
    {
        return Room::where('id', $id)->delete();
    }

    public function enter(Room $room)
    {
        $user = auth()->user();
        if ($room->status !== Room::STATUS_WAITING) {
            return response()->json(['message' => "Room started or closed"], 400);
        }
        if (!blank($user->rooms)) {
            return response()->json(['message' => "You already in room"], 400);
        }
        if ($room->capacity === $room->users->count()) {
            return response()->json(['message' => "fail, room is full"], 400);
        }
        $user->rooms()->attach($room->id);
        return response()->json(['message' => "success"]);
    }
};
