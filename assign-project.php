<?php

$page_name = 'assigned-project';
$title = 'Project List || EOM ';
include 'includes/header.php';

if (!(in_array($page_name, $pageAccessList)) && $roleId != 1) {
  echo '<script>window.location.href = "index.php"</script>';
}

$assign = $conn->prepare('SELECT DISTINCT(`project_id`) FROM `assignproject` WHERE (`user_id` = ? OR `assign_by` = ?)AND `access` = 1');
$assign->execute([$userId, $userId]);
$assign = $assign->fetchAll(PDO::FETCH_ASSOC);
$projects = [];
foreach ($assign as $value) {
  $sql = $conn->prepare('SELECT * FROM `projects` WHERE `id` = ?');
  $sql->execute([$value['project_id']]);
  $projects[] = $sql->fetch(PDO::FETCH_ASSOC);
}


if ($roleId == 1 || in_array('create-task', $pageAccessList)) {
  $access = 0;
} else {
  $access = 1;
}

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
    max-height: 500px;
    overflow-y: scroll;
  }

  .complete {
    background-color: #9ae725 !important;
  }
</style>

<main style="margin-top: 100px;">
  <div class="container pt-1">
    <div class="accordion accordion-flush" id="accordionFlushExample">
      <div class="accordion-item">
        <div class="row">
          <div class="col col-3 border p-3 scroll_bar_for_project" style="width:30%;">
            <div class="text-center">
              <button type="button" class="btn ">
                Project List
              </button>
            </div>
            <?php

            foreach ($projects as $project) {

            ?>
              <div class=" border p-2 w-100 ">
                <h2 class="accordion-header" id="flush-headingThree">
                  <button onclick="fetchTasksAndAddToTable(<?php echo $project['id'] ?> ,<?php echo $project['vector'] ?>)" class="accordion-button p-3" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $project['id'] ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $project['id'] ?>">
                    <a href="project-details.php?project_id=<?php echo base64_encode($project['id']) ?>" style="text-decoration : none; font-size:14px;" class=" projectID">(<?php echo $project['project_name'] ?>)</a>

                  </button>
                </h2>
              </div>

            <?php
            }
            ?>
          </div>


          <div class="col border col-8 p-3" style="width:68%;height: 500px;overflow:auto ">
            <div class="text-center">
              <button type="button" class="btn bg-white btn1  text-center">Task List Under project</button>
            </div>

            <?php

            foreach ($projects as $project) {

            ?>


              <div class=" d-block w-100 ">
                <div id="flush-collapse<?php echo $project['id'] ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo $project['id'] ?>" data-bs-parent="#accordionFlushExample">
                  <div class="accordion-body">
                    <div>
                      <button class="btn btn-primary" style="margin-left: 90%;" onclick="inCompleteProject(<?php echo $project['id'] ?>)">In Complete</button>
                      <p>Project Id: <span><?php echo $project['id'] ?> </span> (<?php echo $project['project_name'] ?>) </p>

                      <table id="dataTable<?php echo $project['id'] ?>" class="table table-striped">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Task Id</th>
                            <th>Area <?php echo strtoupper($project['area']) ?></th>
                            <th>Status</th>
                            <?php
                            if ($project['vector'] == 1) {
                              echo '<th>Vector Status</th>';
                            }
                            ?>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody class="scroll-bar" id="data_insert_<?php echo $project['id'] ?>"></tbody>
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
  function fetchTasksAndAddToTable(id, vector) {
    var test = $('#data_insert_' + id).html();
    console.log(test);
    if (test == '') {
      Notiflix.Loading.dots();
      $.ajax({
        url: 'includes/settings/api/taskApi.php',
        type: 'GET',
        data: {
          type: 'getDataTableComplete',
          project_id: id,
          vector: vector,
          access: <?php echo $access ?>
        },
        success: function(response) {
          $('#data_insert_' + id).html('');
          $('#data_insert_' + id).html(response);
          $('#dataTable' + id).DataTable();
          Notiflix.Loading.remove();
        }
      })
    }
  }

  function deleteTask(task_id, project_id) {
    Notiflix.Confirm.show('EOM Confirm', 'Do you wanrt to delete this task file ?', 'Yes', 'No', () => {
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
  $('.projectID').click(function() {
    window.location.href = $(this).attr('href');
  })
</script>

</body>

</html>