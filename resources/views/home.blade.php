<html>
<head>


	<title>Report a Bug</title>
	<style type="text/css">
		/* apply a natural box layout model to all elements */
		*, *:before, *:after {
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
		}

table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}

	</style>
</head>

<body>
@if($roleId == 1)
	<header>Client Login</header>
<div class="col-md-12">

	<form method='POST' action='./' style="width: fit-content;">
  <input type="hidden" name="_token" value="{{ csrf_token() }}">

		
		<fieldset>
			<label for='issue-description'>Describe your issue below</label>
			<textarea id='issue-description' name='issue-description' rows="4" cols="50"></textarea>
		</fieldset>
		<fieldset>
			
		<button type="button" onclick="submitTask(<?php echo $userId ?>)" >Submit Issue</button>
	</form>
</div>
@elseif($roleId == 2)
<div class="col-md-12">
<h4>Manager Login</h4>
</br>
<h4>Non Assigned Tasks</h4>
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<table style="padding-top:10px;width: fit-content;">
  <tr>
    <th>Task</th>
    <th>Reporter</th>
    <th>Assignee</th>
    <th>Bug CreateAt</th>
  </tr>
  @foreach($tasks as $task)
  <?php 
    // checking for task time 
      date_default_timezone_set("Asia/Kolkata");
      $currentTime= date("Y-m-d H:i:s");
      $taskAssignedTime = $task->createdat;
      $diffTime = (strtotime($currentTime) - strtotime($taskAssignedTime))/60;
      

  ?>
  <tr >
    <td>{{$task->task_name}}</td>
    <td>{{$task->name}}</td>
    <td>
      <select id="managerSel" onchange="assigntoEmp(this,<?php echo $task->id ?>)">
      <option value="0">
        Please select Employee to assign this task
        </option>

      @foreach($assignees as $assignee)

        <option value="{{ $assignee->id }}">
        {{ $assignee->name }}
        </option>

      @endforeach
        
	    </select>
    </td>
    <td>{{$task->createdat}}</tr>
  </tr>
  @endforeach
  
</table>
</br>
<h4> Assigned Tasks</h4>
<table style="padding-top:10px;width: fit-content;">
  <tr>
    <th>Task</th>
    <th>Reporter</th>
    <th>Assignee</th>
    <th>Bug CreateAt</th>
    <th>Status</th>
  </tr>
  @foreach($allAssignedTasks as $task)
  <?php 
    // checking for task time 
      date_default_timezone_set("Asia/Kolkata");
      $currentTime= date("Y-m-d H:i:s");
      $taskAssignedTime = $task->createdat;
      $diffTime = (strtotime($currentTime) - strtotime($taskAssignedTime))/60;
      

  ?>
  <tr <?php  if($diffTime >1) {  ?> style='color:red' <?php }?> >
    <td>{{$task->task_name}}</td>
    <td>{{$task->name}}</td>
    <td>
     {{$task->empname}}
    </td>
    <td>{{$task->createdat}}</td>
    <td>{{$task->status}} </td>
  </tr>
  @endforeach
  
</table>
	</div>

@elseif($roleId == 4)
  <div class="col-md-12">
<h4>Super Manager Login</h4>

<input type="hidden" name="_token" value="{{ csrf_token() }}">

<table style="padding-top:10px;width: fit-content;">
  <tr>
    <th>Bug</th>
    <th>Reporter</th>
    <th>Assignee</th>
    <th>Bug CreateAt</th>
  </tr>
  @foreach($tasks as $task)
  <?php 
    // checking for task time 
      date_default_timezone_set("Asia/Kolkata");
      $currentTime= date("Y-m-d H:i:s");
      $taskAssignedTime = $task->createdat;
      $diffTime = (strtotime($currentTime) - strtotime($taskAssignedTime))/60;
      

  ?>
  <tr <?php  if($diffTime >120) {  ?> style='color:red' <?php }?> >
    <td>{{$task->task_name}}</td>
    <td>{{$task->name}}</td>
    <td>
      <select id="supAdminSel" onchange="assigntoManager(this,<?php echo $task->id ?>)" >
      <option value="0">
        Please select Employee to assign this task
        </option>

      @foreach($assignees as $assignee)

        <option value="{{ $assignee->id }}">
        {{ $assignee->name }}
        </option>

      @endforeach
        
	    </select>
    </td>
    <td>{{$task->createdat}}</tr>
  </tr>
  @endforeach
  
</table>
	</div>



@elseif($roleId == 3)
<div class="col-md-12">
<h4>Employee Login</h4>
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<table style="padding-top:10px;width: fit-content;">
  <tr>
    <th>Task</th>
    <th>Assigned By</th>
    <th>Status</th>
    <th>Assigned At</th>
    <th>Change Status</th>
  </tr>
  @foreach($tasks as $task)
  <tr >
    <td>{{$task->task_name}}</td>
    <td>{{$task->name}}</td>
    <td>{{$task->status}}</td>
    <td>{{$task->emp_assigneddate}}</td>
    <td>
        <select id="empSelect" onchange="changeStatus(this,<?php echo $task->id ?>,<?php echo $userId ?>)">
        <option value="0">Please Select Status to Change</option>
        @foreach($statuses as $status)
        <option value="{{$status->id}}">{{$status->status}}</option>
        @endforeach
        
        </select>
    </td>

  </tr>
  @endforeach
  
</table>
	</div>
@endif
</body>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="js/jquery.floating-share.js" type="text/javascript"></script>

<script>
  function submitTask(id){
    var issueDesc = $('#issue-description').val();
    var userId = id;
  

    $.ajax({
              headers:{'X-CSRF-Token': $('input[name="_token"]').val()},
                type: "POST",
                url: '/submitIssue',
                data: {issueDesc:issueDesc , userId:userId},
                success: function (data)
                {
                  alert(data['message']);
                  location.reload();
                                  
                },
                error: function (response) {
                    alert("Technical Error!");
                }
            });



  }
  function assigntoManager(managerObj,taskId){
    $.ajax({
              headers:{'X-CSRF-Token': $('input[name="_token"]').val()},
                type: "POST",
                url: '/assignToManager',
                data: {managerId:managerObj.value , taskId:taskId},
                success: function (data)
                {
                  alert(data['message']);
                  location.reload();
                                  
                },
                error: function (response) {
                    alert("Technical Error!");
                }
            });
   
  }
  function assigntoEmp(empObj , taskId){
   
    $.ajax({
              headers:{'X-CSRF-Token': $('input[name="_token"]').val()},
                type: "POST",
                url: '/assignToEmployee',
                data: {empId:empObj.value , taskId:taskId},
                success: function (data)
                {
                  alert(data['message']);
                  location.reload();
                                  
                },
                error: function (response) {
                    alert("Technical Error!");
                }
            });
  }
  function changeStatus(statusObj,taskId,empId){
    if(statusObj.value == 0){
     return false;
    }
    $.ajax({
              headers:{'X-CSRF-Token': $('input[name="_token"]').val()},
                type: "POST",
                url: '/changeStatus',
                data: {statusId:statusObj.value , taskId:taskId , empId:empId},
                success: function (data)
                {
                  alert(data['message']);
                  location.reload();
                                  
                },
                error: function (response) {
                    alert("Technical Error!");
                }
            });
    
  }
</script>

</html>
