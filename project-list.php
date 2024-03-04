<?php

$page_name = 'project-list';
$title = 'Project List || EOM ';
include 'includes/header.php';

if (!(in_array($page_name, $pageAccessList)) && $roleId != 1) {
  echo '<script>window.location.href = "index.php"</script>';
}

$sql = $conn->prepare('SELECT * FROM `projects` WHERE `is_complete` = 0 ORDER BY `created_at` DESC');
$sql->execute();
$projects = $sql->fetchAll(PDO::FETCH_ASSOC);


?>
<!-- <link rel="stylesheet" href="css/test2.css"> -->
<style>
  table tbody td .close {
    background-color: rgb(170, 248, 218);
    display: block;
    border-radius: 20px;
    font-size: 16px;
    padding: 0px 8px;
    text-transform: uppercase;
    text-align: center;
  }

  .scroll-bar {
    max-height: 350px;
    overflow: scroll;
  }

  .projectID {
    display: flex;
    margin-left: 20px;
    /* margin-top: 15px; */
    /* position: absolute; */
    font-size: 14px;
    z-index: 9;
  }

  .scroll_bar_for_project {
    /* max-height: 500px; */
    overflow-y: scroll;
  }

  .complete {
    background-color: #9ae725 !important;
  }
</style>

<main style="margin-top: 100px;">
  <div class="container-flude pt-1 px-5">
    <div class="accordion accordion-flush" id="accordionFlushExample">
      <div class="accordion-item">
        <div class="row">
          <div class="col col-3 border p-3 scroll_bar_for_project" style="width:30%;">
            <div class="text-center">

              <p class="bg-white btn1  text-center" style="font-size: 30px;"> Project List</p>
            </div>
            <?php

            foreach ($projects as $project) {

            ?>
              <div class=" border p-2 w-100 ">
                <h2 class="accordion-header" id="flush-headingThree">
                  <button class="accordion-button p-3" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $project['id'] ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $project['id'] ?>">
                    <a href="project-details.php?project_id=<?php echo base64_encode($project['id']) ?>" style="text-decoration : none; font-size:14px;" class=" projectID">(<?php echo $project['project_name'] ?>)</a>

                  </button>
                </h2>
              </div>

            <?php
            }
            ?>
          </div>


          <div class="col border col-8 p-3" style="width:68%;overflow:auto ">
            <div class="text-center">
              <!-- <button type="button" class=" bg-white btn1  text-center">Task List Under project</button> -->
              <p class="bg-white btn1  text-center" style="font-size: 30px;">Task List Under project</p>
            </div>

            <?php

            foreach ($projects as $project) {

            ?>

              <div class=" d-block w-100 ">
                <div id="flush-collapse<?php echo $project['id'] ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo $project['id'] ?>" data-bs-parent="#accordionFlushExample">
                  <div class="accordion-body">
                    <div>
                      <button class="btn btn-primary" style="margin-left: 90%;" onclick="completeProject(<?php echo $project['id'] ?>)">Complete</button>
                      <p>Project Id: <span><?php echo $project['id'] ?> </span> (<?php echo $project['project_name'] ?>) </p>

                      <table id="dataTable" class="table table-striped">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Task Id</th>
                            <th>Area </th>
                            <th>Status</th>
                            <?php
                            if ($project['vector'] == 1) {
                              echo '<th>Vector Status</th>';
                            }
                            ?>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody class="scroll-bar">
                          <?php
                          $i = 0;

                          if ($project['vector'] == 1) {

                            $sql2 = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ? AND `status` <> 'complete' AND `vector_status` <> 'complete' ORDER BY `updated_at` DESC");
                          } else {
                            $sql2 = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ? AND `status` <> 'complete' ORDER BY `updated_at` DESC");
                          }

                          $sql2->execute([$project['id']]);

                          $tasks = $sql2->fetchAll(PDO::FETCH_ASSOC);
                          foreach ($tasks as $task) {

                            $assign = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `role` != 'vector' AND `task_id` = ? AND `status` = 'assign'");
                            $assign->execute([$task['project_id'], $task['task_id']]);
                            $assign = $assign->fetch(PDO::FETCH_ASSOC);

                            $assignVector = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `role` = 'vector' AND `task_id` = ? AND `status` = 'assign'");
                            $assignVector->execute([$task['project_id'], $task['task_id']]);
                            $assignVector = $assignVector->fetch(PDO::FETCH_ASSOC);

                            $user = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
                            $user->execute([$assign['user_id']]);
                            $user = $user->fetch(PDO::FETCH_ASSOC);

                            $Vectoruser = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
                            $Vectoruser->execute([$assignVector['user_id']]);
                            $Vectoruser = $Vectoruser->fetch(PDO::FETCH_ASSOC);

                            $t_id = base64_encode($task['task_id']);
                            $p_id = base64_encode($task['project_id']);

                          ?>
                            <tr>
                              <td><?php echo ++$i ?></td>
                              <th class><?php echo $task['task_id'] ?></th>
                              <td><?php echo substr($task['area_sqkm'], 0, 15) ?> <?php echo $project['area'] ?></td>
                              <td>
                                <span class="close <?php echo $task['status'] ?>" style="background-color :<?php echo $task['status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)'  ?>">
                                  <?php echo str_replace('_', ' ', $task['status']) ?>
                                </span>
                                <span class="close" style="background-color :rgb(235 170 248)">
                                  <?php echo $user['first_name'] . ' ' . $user['last_name'] ?>
                                </span>
                              </td>
                              <?php
                              if ($project['vector'] == 1) {
                              ?>

                                <td>
                                  <span class="close <?php echo $task['status'] ?>" style="background-color :<?php echo $task['vector_status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)'  ?>">
                                    <?php echo str_replace('_', ' ', $task['vector_status']) ?>
                                  </span>
                                  <span class="close" style="background-color :rgb(235 170 248)">
                                    <?php echo $Vectoruser['first_name'] . ' ' . $Vectoruser['last_name'] ?>
                                  </span>
                                </td>
                              <?php
                              }
                              ?>
                              <td style="display: flex;justify-content: space-between;">
                                <a href="task-details.php?task_id=<?php echo $t_id ?>&project_id=<?php echo $p_id ?>"><i style="color: black;" class="fas fa-info-circle"></i></a>
                                <?php
                                if ($roleId == 1 || in_array('create-task', $pageAccessList)) {
                                ?>
                                  <a href="create-task.php?edit=<?php echo $t_id ?>"><i style="color: #0001fa;" class="fas fa-edit">
                                    </i></a><a onclick="deleteTask('<?php echo $task['task_id'] ?>','<?php echo $task['project_id'] ?>')"><i style="color: #dd0000;" class="fas fa-trash"></i></a>
                                <?php
                                }
                                ?>
                              </td>
                            </tr>
                          <?php
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

            <?php

            }

            ?>

          </div>

        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/footer.php' ?>

<script>
  $('table').DataTable();

  function deleteTask(task_id, project_id) {
    Notiflix.Confirm.show('EOM Confirm', 'Do you want to delete this task file ?', 'Yes', 'No', () => {
      $.ajax({
        url: 'includes/settings/api/taskApi.php',
        type: 'POST',
        dataType: 'json',
        data: {
          type: 'deleteTask',
          task_id: task_id,
          project_id: project_id
        },
        success: function(data) {
          notyf.success(data.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(error) {
          console.error('Error deleting data:', error);
        }
      });
    });
  }

  function completeProject(id){
    Notiflix.Confirm.show('EOM Confirm', 'Do you want to Complete this Project ?', 'Yes', 'No', () => {
      $.ajax({
        url: 'includes/settings/api/projectApi.php',
        type: 'POST',
        dataType: 'json',
        data: {
          type: 'completeProject',
          id: id
        },
        success: function(data) {
          notyf.success(data.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(error) {
          console.error('Error deleting data:', error);
        }
      });
    });
  }

  $('.projectID').click(function() {
    window.location.href = $(this).attr('href');
  })
</script>

</body>

</html>