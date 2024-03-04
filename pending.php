<?php 
  $title = 'Pending Application || EOM ';
  $page_name = 'pending';
  include "includes/header.php";
  $user = $conn->prepare("SELECT * FROM `leaves` WHERE `user_id` = ? AND `status`='pending'");
    $user->execute([$userId]);
    $result = $user->fetchAll(PDO::FETCH_ASSOC);
?>
     
  <main style="margin-top: 75px;">
    <div class="container-flude ">
      <section class="vh-100 gradient-custom">
        <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
                
          <a href="leave.php" class=" text-decoration-none px-3 py-2 mx-1  btn-outline-primary">Apply</a>
          <a href="#" class=" text-decoration-none px-3 py-2 mx-1 btn-outline-primary active">Pending</a>
          <a href="history.php" class=" text-decoration-none px-3 py-2 mx-1   btn-outline-primary">History</a>
          <!-- <button type="button" class="btn btn-primary">Pending</button>
          <button type="button" class="btn btn-primary">History</button> -->
        </div>
          <div class="container-flude  h-100">
            <div class="row justify-content-center mt-3 h-100">
              <div class="col-12 col-lg-9 col-xl-10">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                  <div class="card-body p-2">
                    <!-- <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Registration Form</h3> -->
                    <form>
                      
                        <?php
                          foreach($result as $item){
                            $date1 = new DateTime($item['form_date']);
                            $date2 = new DateTime($item['end_date']);

                            $interval = $date1->diff($date2);
                            $daysDifference = $interval->days;

                            if($item['enddate_session'] == 'Second Half' && $item['formdate_session'] == 'First Half'){
                              $daysDifference += 1;
                            }
                            
                            if($item['formdate_session'] == 'Second Half' && $item['enddate_session'] == 'First Half'){
                              $daysDifference += 1;
                            }
                
                            if($item['formdate_session'] == $item['enddate_session']){
                              $daysDifference += 0.5;
                            }

                            echo '
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                              <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne_'.$item['id'].'" aria-expanded="false" aria-controls="flush-collapseOne">
                                    <div class="row box1  ">
                                      <div class="col-3 "><span class="text-muted">Category</span><p>Leave</p></div>
                   
                                      <div class="col-3"><span class="text-muted">No. of day</span><p>'.$daysDifference.'</p></div>
                                      <div class="col-3 mt-2 "><span class=" color" style="color:#ffcc00">'.strtoupper($item['status']).'</span></div>
                                    
                                    </div>
                                  </button>
                                </h2>
                                <div id="flush-collapseOne_'.$item['id'].'" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                  <div class="accordion-body mx-1 py-1">
                                      <div class="d-flex">
                                        <span class="fw-bold ">Duration : </span><p class="mx-2"><strong>'.date("d M Y",strtotime($item['form_date'])).'</strong>('.$item['formdate_session'].') to  <strong>'.date("d M Y",strtotime($item['end_date'])).'</strong> ('.$item['enddate_session'].')</p>
                                      </div>
                                      <div class="d-flex ">
                                          <span class="fw-bold">Reason : </span><p class="mx-2">'.$item['region'].'</p>
                                      </div>
                                  </div>
                                  <hr>
                                  <div class="accordion-body mx-1">
                                      <div class="d-flex justify-content-between justify-content-center">
                                        <div class="">
                                          <strong>Applied on</strong><p class="">'.date("d M Y h:i A",strtotime($item['created_at'])).'</p>
                                        </div>
                                        <div class="details mt-4"><a href="" class="">View Less</a></div>
                                      </div>
                                    
                                  </div>
                                </div>
                              </div>
                              </div>
                            ';
                          }
                        ?>
                          
                        
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
    </div>
  </main>
   

    <?php include 'includes/footer.php' ?>

</body>
</html>