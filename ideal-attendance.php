<?php
    $page_name = 'ideal-attendance';
    $title = 'Ideal Hour || EOM ';
    include 'includes/header.php' ;
    if(!(in_array($page_name, $pageAccessList)) && $roleId != 1){
        echo '<script>window.location.href = "index.php"</script>';
    }  
?>
  <main style="margin-top: 100px;">
    <div class="container pt-5">
            <div class="container">
                
            </div>
    </div>
  </main>
<?php include 'includes/footer.php' ?>