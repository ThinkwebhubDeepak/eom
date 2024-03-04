<?php 
  $title = 'Leaves || EOM ';
  $page_name = 'apply-leave';
  include "includes/header.php" ;
  $leave  = $conn->prepare("SELECT COUNT(*) AS leaves_count FROM leaves WHERE (YEAR(form_date) = YEAR(CURDATE()) OR YEAR(end_date) = YEAR(CURDATE())) AND `status` = 'approve' AND `user_id` = ?");
  $leave->execute([$userId]);
  $leave = $leave->fetch(PDO::FETCH_ASSOC);

  $userD  = $conn->prepare("SELECT * FROM `userdetails` WHERE `user_id` = ?");
  $userD->execute([$userId]);
  $userD = $userD->fetch(PDO::FETCH_ASSOC);
  print_r($userD);
?>
     
  <main style="margin-top: 75px;">
    <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
      <a href="#" class=" text-decoration-none px-3 py-2 mx-1 btn-outline-primary active">Apply</a>
      <a href="pending.php" class=" text-decoration-none px-3 py-2 mx-1 btn-outline-primary">Pending</a>
      <a href="history.php" class=" text-decoration-none px-3 py-2  mx-1  btn-outline-primary">History</a>
    </div>
    <div class="container-flude ">
    <section class="gradient-custom">    
      <div class="container-flude py-5 px-5">
        <div class="row justify-content-center align-items-center">
          <div class="col-12 col-lg-9 col-xl-10">
            <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
              <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between">
                  <h3 class="mb-4 pb-2 pb-md-0  page_heading">Applying for Leave</h3>
                  <div style="align-items: center;display: flex; gap: 10px;">
                    <div class="badge bg-primary text-wrap" style="width: 6rem;">
                      Available Leave : <?php echo $userD['leave_balance'] ?>
                    </div>

                    <!-- <a class="btn btn-primary" onclick="halfDay()">Half Day</a> -->
                  </div>
                </div>
                <form id="leaveForm">
                  <!-- <div class="input-box">
                      <label class="form-label select-label">Leave type</label>
                      <select class="select form-control " name="leave_type" required>
                        <option value="" disabled>Choose option</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Unpaid Leave">Unpaid Leave</option>
                      </select>
                  </div> -->
    
                  <div class="col-md-12 col-sm-12 mb-2 me-3 ">
                    <input type="text" class="form-control" id="days_leave" readonly>
                  </div>
                  <div class="d-flex input-box">
                    <div class="col-md-12 col-sm-12 mb-2 me-3 ">
                      <label class="form-label select-label">From date</label>
                      <input type="text" class="width-200 form-control"id="datepicker" name="form_date" placeholder="Select Start Date" required>
                      <input type="hidden" name="type" value="addLeave">
                    </div>
                    <div class="col-md-6 mb-2" style="display:none">   
                      <label class="form-label select-label">Sessions</label>
                        <select class="select form-control width-200" name="formdate_session" id="formdate_session" required>
                          <option value="" disabled>Choose option</option>
                          <option value="First Half" selected>First Half</option>
                          <option value="Second Half">Second Half</option>
                        </select>
                    </div>
                  </div>
                  
                  <div class="d-flex input-box">
                    <div class="col-md-12 col-sm-12 mb-2 me-3 " >
                      <label class="form-label select-label">End date</label>
                      <input type="text" class="width-200 form-control" id="datepicker1"  name="end_date" placeholder="Select End Date" required>
                    </div>
                    <div class="col-md-6 mb-2" style="display:none">   
                      <label class="form-label select-label">Sessions</label>
                        <select class="select form-control width-200" name="enddate_session" id="enddate_session" required>
                          <option value="" disabled>Choose option</option>
                          <option value="First Half">First Half</option>
                          <option value="Second Half" selected>Second Half</option>
                        </select>
                    </div>
                  </div>

                    <!-- <div class=" input-box">
                      <img src="https://miro.medium.com/v2/resize:fill:224:224/0*K0cFJ7X5gQoJ3CtG.jpg" alt="img" class="images">
                      <select class="select form-control-sm border-0">
                          <option value="1" >Choose option</option>
                          <option value="2">Subject 1</option>
                          <option value="3">Subject 2</option>
                          <option value="4">Subject 3</option>
                        </select>
                    </div> -->

                    <!--<div class="mb-2 input-box">
                      <label for="formFileDisabled" class="form-label">Cc to</label>
                      <input class="form-control" type="file" id="formFileDisabled" name="upload">
                    </div>-->
                    <div class="d-flex input-box">
                      <div class="col-md-6 col-sm-12 mb-2 me-3 ">
                        <p class="form-label select-label">Contact details</p>
                        <input type="text" class="form-control" placeholder="Enter Contact details" name="contact_detail" required>
                      </div>
                      <div class="col-md-6 mb-2">   
                        <label class="form-label select-label">Reason</label>
                        <input type="text" class="form-control" placeholder="Enter a Reason" name="region" required>
                      </div>
                    </div>
                    <div class="d-flex ms-5">
                  <div class="mt-2 pt-1 me-4">
                    <input class="btn btn-primary btn-lg" type="submit" value="Submit" />
                  </div>
                  <div class="mt-2 pt-1  me-4">
                      <input class="btn btn-primary btn-lg" type="reset" value="Cancel" />
                    </div>
                  </div>
                  </div>
    
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
  <script>
    $('#datepicker').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  
  $('#datepicker1').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
    $("#leaveForm").submit(function(){
      event.preventDefault();
      var formData = new FormData(this);
				$.ajax({
					url: 'includes/settings/api/leaveApi.php',
					type: 'POST',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function (response) {
						notyf.success(response.message);
            setTimeout(() => {
              window.location = 'pending.php';
            }, 1500);
					},
					error: function(xhr, status, error) {
						var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
						notyf.error(errorMessage);
					}
				});
    });



    function calculateDaysDifference(startDate, endDate, startSession, endSession) {
      var start = new Date(startDate);
      var end = new Date(endDate);
      var timeDifference = end - start;
      var daysDifference = timeDifference / (1000 * 60 * 60 * 24);

      // Adjust time difference based on selected sessions
      // if (startSession === "Second Half" && endSession === "First Half") {
      //   daysDifference += 0.5;
      // }else if (startSession === "First Half" && endSession === "Second Half") {
      //   daysDifference += 1;
      // }   
      
      if (startSession === endSession) {
        daysDifference += 0.5;
      }else if (startSession === "Second Half" && endSession === "First Half") {
        daysDifference -= 0;
      } else{
        daysDifference += 1;
      }

      return daysDifference;
    }

    // Function to calculate leave day count
    function calculateLeaveDays() {
      var startDate = $("#datepicker").val();
      var endDate = $("#datepicker1").val();
      var startSession = $("#formdate_session").val();
      var endSession = $("#enddate_session").val();

      // Perform your leave day count calculation here
      var leaveDayCount = calculateDaysDifference(startDate, endDate, startSession, endSession);

      // Print leave day count to the days_leave input
      $("#days_leave").val(leaveDayCount + ' Days');
    }

    // Attach the calculateLeaveDays function to the change event of datepicker, datepicker1, formdate_session, and enddate_session
    $("#datepicker, #datepicker1, #formdate_session, #enddate_session").change(function () {
      calculateLeaveDays();
    });

    function halfDay(){
      
    }


  </script>
</body>
</html>