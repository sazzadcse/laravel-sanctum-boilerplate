<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Http\Controllers\HttpResponseCodes;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $limit = ( isset( $request->limit ) && !empty( $request->limit ) ) ? $request->limit  : 20; 
        $offset = ( isset( $request->page ) && !empty( $request->page ) ) ? ($request->page * $limit) - $limit : 0;
        
        //start query here..
        $tasks      = Task::where('user_id', Auth::user()->id)->offset($offset)->limit($limit)->get();
        $totaTasks  = Task::where('user_id', Auth::user()->id)->get()->count();

        return $this->success([
            'meta' => [
                'page'   => $offset + 1,
                'limit'  => $limit,
                'total'  => $totaTasks,
            ],
            'attributes' => $tasks,
        ], 'Data found successfully', 200);

        /**
         * or can send your response data by resource collection 
         * you can customize your collection as your requrement
         */
        //return TaskResource::collection($tasks);
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request) {
        $request->validated($request->all());

        $task = Task::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        // return $this->success($task, 'Task created successfully', 200);  // you can send your cusmize response by HttpResponses
        return new TaskResource($task); // or you can send your response data by resource collection
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task) {
        return ($this->checkAuthorization($task)) ? $this->checkAuthorization($task) : new TaskResource($task);
        // return $this->success($task, 'Data found successfully', 200);  // you can send your cusmize response by HttpResponses
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task) {
        if ( $this->checkAuthorization($task) ) {
            return $this->checkAuthorization($task);
        }

        $task->update($request->all());

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task) {
        if ( $this->checkAuthorization($task) ) {
            return $this->checkAuthorization($task);
        } 
        $task->delete();

        return $this->success('', 'Task deleted successfully', 200);
    }

    /**
     * Check the authorization from storage.
     *
     * @param  object  $task
     * @return bool
     */
    private function checkAuthorization($task) {
        if (!$task || Auth::user()->id !== $task->user_id ) {
            return $this->error('', 'You are not authorized to make this request.', 403);
        }
    }
    
}
