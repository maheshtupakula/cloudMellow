<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use DB;


class CloudMellowController extends Controller
{
   


    public function loginAction(Request $request){
       $username = $request->uname;
       $password  = $request->psw;
       $checkUserExist = DB::table('users')
            ->select('id','name','role_id')
            ->where('name',$username)
            ->where('password',$password)
            ->get();
        if(count($checkUserExist) > 0){
            $roleId = $checkUserExist[0]->role_id;
            if($roleId == 1){   // client Login
                return view('home')
                ->with('roleId', $roleId)
                ->with('userId',$checkUserExist[0]->id)
                ->with('username',$username)
                ->with('assignees',[])
                ->with('tasks',[]);
            }elseif($roleId == 2){ // manager login
                $getAllTasks = DB::table('tasks')
                        ->select('tasks.task_name','tasks.createdat','tasks.manager_id','users.name','tasks.id') 
                        ->join('users','users.id','tasks.user_id')
                        ->where('manager_id',$checkUserExist[0]->id)
                        ->where('emp_id','=',null)
                        ->get();
                $getAllAssignees = DB::table('users')
                                ->where('role_id',3)
                                ->get();
            
                $getAllAssignedTasks = DB::select(DB::raw("select ts.id,ts.task_name,ts.user_id,ts.emp_id,ts.manager_id,ts.emp_assigneddate as createdat,tst.status,us.name,ur.name as empName
                                     from tasks as ts inner join users as us on us.id=ts.user_id
                                      inner join users as ur on ts.emp_id=ur.id
                                      inner join task_statuses as tst on tst.id=ts.status
                          where manager_id  = ".$checkUserExist[0]->id ." and emp_id is not null"));
                return view('home')
                ->with('roleId', $roleId)
                ->with('username',$username)
                ->with('tasks',$getAllTasks)
                ->with('allAssignedTasks',$getAllAssignedTasks)
                ->with('assignees',$getAllAssignees);   

            }elseif($roleId == 3){   //emp login
                $getAllTasksAssignedToEmp = DB::table('tasks')
                ->select('tasks.id','tasks.task_name','tasks.emp_assigneddate','tasks.manager_id','users.name','task_statuses.status')
                ->join('users','users.id','tasks.manager_id')
                ->join('task_statuses','task_statuses.id','tasks.status')  
                ->where('emp_id',$checkUserExist[0]->id)
                ->get();
                $getAllStatuses = DB::table('task_statuses')->get();
            return view('home')
                ->with('roleId', $roleId)
                ->with('username',$username)
                ->with('statuses',$getAllStatuses)
                ->with('userId',$checkUserExist[0]->id)
                ->with('tasks',$getAllTasksAssignedToEmp);  
            }

            elseif($roleId == 4){   //supermanager login
                $getAllTasks = DB::table('tasks')
                        ->select('tasks.task_name','tasks.createdat','tasks.manager_id','users.name','tasks.id') 
                        ->join('users','users.id','tasks.user_id')
                        ->where('manager_id','=',NULL)
                        ->get();
                $getAllAssignees = DB::table('users')
                                ->where('role_id',2)
                                ->get();
                
            return view('home')
                ->with('roleId', $roleId)
                ->with('username',$username)
                ->with('tasks',$getAllTasks)
                ->with('assignees',$getAllAssignees); 
            }
            
            
        }else{
            return "Login Failed";
        }

        

    }
    public function submitIssue(Request $request){
        $issueDesc = $request->issueDesc;
        $userId = $request->userId;
        date_default_timezone_set("Asia/Kolkata");
        $currentTime = date('Y-m-d H:s:i');
        $issueArray = ['task_name'=>$issueDesc , 'user_id'=>$userId , 'createdat'=>$currentTime , 'status'=>1];
        $saveIssue= DB::table('tasks')->insert($issueArray);
        if($saveIssue){
            return ['status'=>200 , 'message'=>'Issue Saved Succesfully'];
        }else{
            return ['status'=>400 , 'message'=>'Issue Not Saved'];
        }

    }
    public function assignToManager(Request $request){
        $managerId = $request->managerId;
        $taskId = $request->taskId;
        date_default_timezone_set("Asia/Kolkata");
        $currentTime = date('Y-m-d H:s:i');

        try{
       $updateIsuue = DB::table('tasks')
                        ->where('id',$taskId)
                        ->update(['manager_id'=>$managerId , 'manager_assigneddate'=>$currentTime]);
        return ['status'=>200 , 'message'=>'Issue Updated'];

        }catch(\Exception $e){

            return['status'=>400 , 'message'=>'Failed to Update Issue'];


        }
                        
    
    }
    public function assignToEmployee(Request $request){
        $empId = $request->empId;
        $taskId = $request->taskId;
        date_default_timezone_set("Asia/Kolkata");
        $currentTime = date('Y-m-d H:s:i');
        try{
       $updateIsuue = DB::table('tasks')
                        ->where('id',$taskId)
                        ->update(['emp_id'=>$empId , 'emp_assigneddate'=>$currentTime]);
        return ['status'=>200 , 'message'=>'Issue Updated'];

        }catch(\Exception $e){
            dd($e->getMessage());
            return['status'=>400 , 'message'=>'Failed to Update Issue'];


        }
                        
    
    }
    public function changeStatus(Request $request){
        $statusId = $request->statusId;
        $taskId = $request->taskId;
        $empId = $request->empId;
        try{
        $updateStatus = DB::table('tasks')
                        ->where('id',$taskId)
                        ->where('emp_id',$empId)
                        ->update(['status'=>$statusId]);
        return ['status'=>200 , 'message'=>'Status updated'];
        }catch(\Exception $e){
            return ['status'=>200 , 'message'=>'Status update failed'];

        }
    }
}
