

<script src="includes/assets/plugin/jquery.js"></script>
<script src="includes/assets/plugin/new_bootstrap.js"></script>
<script src="includes/assets/plugin/font-awesome-all.js"></script>
<script src="includes/assets/plugin/datatables.min.js"></script>
<script src="includes/assets/plugin/select2.js"></script>
<script src="includes/assets/plugin/notifix.js"></script>
<script src="includes/assets/plugin/notify.js"></script>
<script src="includes/assets/plugin/datedropper-jquery.js"></script>

<script>
    var dataTable = $('#dataTable').DataTable();
    var notyf = new Notyf({ position: { x: 'right', y: 'top'} });
    function seenNotifcation(){
        $.ajax({
            url : 'includes/settings/api/notificationApi.php',
            type: 'post',
            data : {type : 'seenNotification'},
        });
    }
</script>
