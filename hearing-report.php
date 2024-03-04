<?php
$current_page = "hearing-test";
$accessPage = true;
$title = "Audium - Hearing Test Page";
include "settings/config.php";
$check = $conn->prepare("SELECT * FROM `hearing_test` WHERE `crno` = ?");
$check->execute([$_GET['crno']]);
$result = $check->fetch(PDO::FETCH_ASSOC);
if (!$result) {
    echo "Report is not found. Please check CR No.";
}
?>

<?php include "./settings/header.php"; ?>
<?php
if ($UserAccessType != "admin") {
    echo '<script>window.location.href = "index.php";</script>';
    exit();
}
?>
<?php include "./settings/sidebar.php"; ?>
<style>
    .displayData li {
        display: flex;
        justify-content: space-between;
    }

    .displayData li div {
        width: 200px;
        font-weight: 200;
    }

    .displayData li .title {
        width: 200px;
        font-weight: 600;
    }

    input[type="number"] {
        width: 100%;
    }

    .inputData th {
        width: 80px;
    }

    .twt {
        display: flex;
        gap: 10px;
    }

    .twt .datafield {
        width: 40%;
    }

    .twt .report {
        width: 60%;
    }

    .display_audio_data td,
    .display_audio_data th {
        width: 100px;
        border: 1px solid;
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
<!-- Page Wrapper -->
<!-- Page Wrapper -->
<style>
    .chart-wrap {
        font-family: Arial;
        font-size: 12px;
        /* position: absolute; */
        left: 50%;
        width: 100%;
        height: 300px;
        border-left: solid 1px #616161;
        border-bottom: solid 1px #616161;
        margin-top: 20px;
        /* margin-left: -300px; */
    }

    .chart-wrap .chartGrap {
        z-index: 2;
        position: absolute;
        /* bottom: 0; */
        display: block;
        margin-top: 5px;
        width: auto;
        height: auto;
    }

    .chart-wrap .left-side {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: inherit;
    }

    .chart-wrap .left-side span {
        display: flex;
        border-bottom: solid 1px#616161;
        /* width: 100%; */
        /* margin-left: -40px; */
        align-items: flex-end;
        text-indent: -25px;
    }

    .chart-wrap .bottom-side {
        width: 100%;
        height: auto;
        /* position: absolute; */
        /* left: 0; */
        /* bottom: -30px; */
        display: flex;
        align-items: flex-end;
        margin-top: -300px;
    }

    .chart-wrap .bottom-side span {
        width: 18%;
        text-align: left;
        display: grid;
        align-items: end;
        text-indent: -10px;
    }

    span.normal {
        border-right: solid 1px#616161;
    }

    span.half {
        width: 9% !important;
        border-right: solid 1px#616161;
        /* border-left: dashed 1px#616161 !important; */
        text-indent: -20px;
    }

    span.lborder {
        width: 9% !important;
        border-right: dashed 1px#616161;
        text-indent: -20px;
    }

    span p {
        margin: 0;
        margin-bottom: -20px;
    }

    span.end {
        border: none;
    }

    #myPolyline1 {
        stroke: red;
    }
</style>
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-7">
                    <h3 class="page-title">Hearing Test</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="javascript:(0);">Diagnostic</a></li>
                        <li class="breadcrumb-item active">Hearing Test</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row report" id="printableArea">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="">
                            <div class="row">
                                <div class="col-sm-2 m-b-20">
                                    <img alt="Logo" class="inv-logo img-fluid" src="assets/img/logo.png">
                                </div>
                                <div class="col-sm-8 m-b-20" style="text-align:center">
                                    <div class="">
                                        <h3 class="text-uppercase">
                                            Audium Clinic
                                        </h3>
                                        <p class="text-uppercase">
                                            A Specoality Center for speech therpy and
                                            hearing care.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-2 m-b-20">
                                    <img alt="Logo" class="inv-logo img-fluid" src="assets/img/logo.png">
                                </div>
                            </div>
                            <hr>
                            <div class="row displayData">
                                <div class="col-sm-4 m-b-20">
                                    <ul class="list-unstyled mb-0">
                                        <li>
                                            <div class="title">Name</div> : <div id="report_name"><?php echo $result['name']; ?></div>
                                        </li>
                                        <li>
                                            <div class="title">Sex</div> : <div id="report_sex"><?php echo $result['sex']; ?></div>
                                        </li>
                                        <li>
                                            <div class="title">Test By</div> : <div id="report_testby"><?php echo $result['test_by']; ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-sm-4 m-b-20">
                                    <ul class="list-unstyled mb-0">
                                        <li>
                                            <div class="title">CR No.</div> : <div id="report_crno"><?php echo $result['crno']; ?></div>
                                        </li>
                                        <li>
                                            <div class="title">Adult/Child</div> : <div id="report_adult"><?php echo $result['adult']; ?>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="title">Referred By</div> : <div id="report_referredby"><?php echo $result['referredby']; ?>

                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-sm-4 m-b-20">
                                    <ul class="list-unstyled mb-0">
                                        <li>
                                            <div class="title">TestDate</div> : <div class="testDate">
                                                <?php echo $result['created_at']; ?>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="title">Age</div> : <div class="age" id="report_age"><?php echo $result['age']; ?></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6 col-lg-6 col-xl-6 m-b-20">
                                    <h2>Right</h2>
                                    <div class="chart-wrap">
                                        <svg class="chartGrap" id="chartGrap">
                                            <polyline id="rightfreq" fill="none" stroke="red" stroke-width="2">
                                            </polyline>
                                            <polyline id="rightbcr" fill="none" stroke="red" stroke-width="2">
                                            </polyline>
                                        </svg>

                                        <div class="left-side">
                                            <span style="border-top: 1px solid;">-10</span>
                                            <span>0</span>
                                            <span>10</span>
                                            <span>20</span>
                                            <span>30</span>
                                            <span>40</span>
                                            <span>50</span>
                                            <span>60</span>
                                            <span>70</span>
                                            <span>80</span>
                                            <span>90</span>
                                            <span>100</span>
                                            <span>110</span>
                                            <span>120</span>
                                        </div>
                                        <div class="bottom-side">
                                            <span class="normal">
                                                <p>125</p>
                                            </span>

                                            <span class="normal">
                                                <p>250</p>
                                            </span>

                                            <span class="lborder">
                                                <p>500 </p>
                                            </span>

                                            <span class="half">
                                                <p> 750 </p>
                                            </span>
                                            <span class="lborder">
                                                <p>1k </p>
                                            </span>

                                            <span class="half">
                                                <p> 1.5k </p>
                                            </span>
                                            <span class="lborder">
                                                <p>2k </p>
                                            </span>

                                            <span class="half">
                                                <p> 3k </p>
                                            </span>
                                            <span class="lborder">
                                                <p> 4k </p>
                                            </span>

                                            <span class="half">
                                                <p> 6k </p>
                                            </span>
                                            <span class="lborder end">
                                                <p>8k </p>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-6 col-xl-6 m-b-20">
                                    <h2>Left </h2>
                                    <div class="chart-wrap">
                                        <svg class="chartGrap" id="chartGrap2">
                                            <polyline id="leftfreq" fill="none" stroke="#0074d9" stroke-width="2">
                                            </polyline>
                                            <polyline id="leftbcr" fill="none" stroke="#0074d9" stroke-width="2">
                                            </polyline>
                                        </svg>

                                        <div class="left-side">
                                            <span style="border-top: 1px solid;">-10</span>
                                            <span>0</span>
                                            <span>10</span>
                                            <span>20</span>
                                            <span>30</span>
                                            <span>40</span>
                                            <span>50</span>
                                            <span>60</span>
                                            <span>70</span>
                                            <span>80</span>
                                            <span>90</span>
                                            <span>100</span>
                                            <span>110</span>
                                            <span>120</span>
                                        </div>
                                        <div class="bottom-side">
                                            <span class="normal">
                                                <p>125</p>
                                            </span>

                                            <span class="normal">
                                                <p>250</p>
                                            </span>

                                            <span class="lborder">
                                                <p>500 </p>
                                            </span>

                                            <span class="half">
                                                <p> 750 </p>
                                            </span>
                                            <span class="lborder">
                                                <p>1k </p>
                                            </span>

                                            <span class="half">
                                                <p> 1.5k </p>
                                            </span>
                                            <span class="lborder">
                                                <p>2k </p>
                                            </span>

                                            <span class="half">
                                                <p> 3k </p>
                                            </span>
                                            <span class="lborder">
                                                <p>4k </p>
                                            </span>

                                            <span class="half">
                                                <p> 6k </p>
                                            </span>
                                            <span class="lborder end">
                                                <p>8k </p>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12 m-b-20 display_audio_data">
                                    <h4 class="d-flex justify-content-between w-100"> Right <span id="rfr_pta">PTA = </span></h4>
                                    <table>
                                        <tr>
                                            <th>Freq</th>
                                            <td>125</td>
                                            <td>250</td>
                                            <td>500</td>
                                            <td>750</td>
                                            <td>1K</td>
                                            <td>1.5K</td>
                                            <td>2K</td>
                                            <td>3K</td>
                                            <td>4K</td>
                                            <td>6K</td>
                                            <td>8K</td>
                                        </tr>
                                        <tbody>
                                            <tr>
                                                <th>R</th>
                                                <td id="rfr_0"></td>
                                                <td id="rfr_1"></td>
                                                <td id="rfr_2"></td>
                                                <td id="rfr_3"></td>
                                                <td id="rfr_4"></td>
                                                <td id="rfr_5"></td>
                                                <td id="rfr_6"></td>
                                                <td id="rfr_7"></td>
                                                <td id="rfr_8"></td>
                                                <td id="rfr_9"></td>
                                                <td id="rfr_10"></td>
                                            </tr>
                                            <tr>
                                                <th>BCR</th>
                                                <td id="rbcr_0"></td>
                                                <td id="rbcr_1"></td>
                                                <td id="rbcr_2"></td>
                                                <td id="rbcr_3"></td>
                                                <td id="rbcr_4"></td>
                                                <td id="rbcr_5"></td>
                                                <td id="rbcr_6"></td>
                                                <td id="rbcr_7"></td>
                                                <td id="rbcr_8"></td>
                                                <td id="rbcr_9"></td>
                                                <td id="rbcr_10"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-12 m-b-20 display_audio_data">
                                    <h4 class="d-flex justify-content-between w-100">Left <span id="lfr_pta">PTA = </span></h4>
                                    <table>
                                        <tr>
                                            <th>Freq</th>
                                            <td>125</td>
                                            <td>250</td>
                                            <td>500</td>
                                            <td>750</td>
                                            <td>1K</td>
                                            <td>1.5K</td>
                                            <td>2K</td>
                                            <td>3K</td>
                                            <td>4K</td>
                                            <td>6K</td>
                                            <td>8K</td>
                                        </tr>
                                        <tbody>
                                            <tr>
                                                <th>L</th>
                                                <td id="lfr_0"></td>
                                                <td id="lfr_1"></td>
                                                <td id="lfr_2"></td>
                                                <td id="lfr_3"></td>
                                                <td id="lfr_4"></td>
                                                <td id="lfr_5"></td>
                                                <td id="lfr_6"></td>
                                                <td id="lfr_7"></td>
                                                <td id="lfr_8"></td>
                                                <td id="lfr_9"></td>
                                                <td id="lfr_10"></td>
                                            </tr>
                                            <tr>
                                                <th>BCR</th>
                                                <td id="lbcr_0"></td>
                                                <td id="lbcr_1"></td>
                                                <td id="lbcr_2"></td>
                                                <td id="lbcr_3"></td>
                                                <td id="lbcr_4"></td>
                                                <td id="lbcr_5"></td>
                                                <td id="lbcr_6"></td>
                                                <td id="lbcr_7"></td>
                                                <td id="lbcr_8"></td>
                                                <td id="lbcr_9"></td>
                                                <td id="lbcr_10"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <h4>Provisional Diagnosis :</h4>
                                <p id="report_provisional_diagnosis"><?php echo $result['provisional_diagnosis'] ?></p>
                            </div>
                            <div class="row mt-3">
                                <h4>Recommendation :</h4>
                                <p id="report_recommendation"><?php echo $result['recommendation'] ?></p>
                            </div>
                            <div class="row">
                                <h5 style="text-align:right">Consultant Audiologist</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="btn btn-primary" onclick="printDiv()">Print</button>
    </div>
</div>
<!-- /Page Wrapper -->

<!-- Delete Modal -->
<div class="modal fade" id="delete_modal" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-content p-2">
                    <h4 class="modal-title">Delete</h4>
                    <p class="mb-4">Are you sure want to delete?</p>
                    <button type="button" id="delete_btn" class="btn btn-primary" delete-data="3">Delete </button>
                    <button type="button" id="close_delete_btn" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Delete Modal -->

<?php include "./settings/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var notyf = new Notyf({
        position: {
            x: 'right',
            y: 'top'
        }
    });
</script>
<script>

    
    function updateSVGSize() {
        const container = document.querySelector(".chart-wrap");
        var containerWidth = container.clientWidth;
        var containerHeight = container.clientHeight;
        $('svg image').remove()
        $(".chartGrap").css('width', containerWidth + "px");
        $(".chartGrap").css('height', containerHeight + "px");
        $(".bottom-side span").css('height', (containerHeight) + "px")
        var dbleftFreqPoints = JSON.parse('<?php echo $result['lfreq'] ?>');
        var dbrightFreqPoints = JSON.parse('<?php echo $result['rfreq'] ?>');
        var dbleftBcrPoints = JSON.parse('<?php echo $result['lbcr'] ?>');
        var dbrightBcrPoints = JSON.parse('<?php echo $result['rbcr'] ?>');

        $('#rightfreq image').remove();


        addRFeq(dbrightFreqPoints, 'rfr_');
        addRFeq(dbrightBcrPoints, 'rbcr_');
        addRFeq(dbleftFreqPoints, 'lfr_');
        addRFeq(dbleftBcrPoints, 'lbcr_');

        createDataPoints(leftfreq, dbleftFreqPoints, container, '<?php echo $result['lfreq_icon'] ?>');
        createDataPoints(rightfreq, dbrightFreqPoints, container, '<?php echo $result['rfreq_icon'] ?>');
        createDataPoints(leftbcr, dbleftBcrPoints, container, '<?php echo $result['lbcr_icon'] ?>');
        createDataPoints(rightbcr, dbrightBcrPoints, container, '<?php echo $result['rbcr_icon'] ?>');
    }

    updateSVGSize();
    window.addEventListener("resize", updateSVGSize);

    function addRFeq(dbrightFreqPoints, id) {
        let x = 0;
        let pta = 0;
        dbrightFreqPoints.forEach(element => {
            if(x == 2 || x == 4 || x == 6){
                pta += element;
            }
            $('#' + id + x).html(element);
            x++;
        });
        $('#'+id+'pta').text('PTA = '+pta/3);
    }




    function printDiv() {

        var printWindow = window.open('', '', 'width=950,height=950');
        var printContent = document.getElementsByTagName('html')[0].innerHTML;
        // var docu = document.getElementsByTagName('html')[0];
        // var printContent = new XMLSerializer().serializeToString(docu);
        printWindow.document.open();
        printWindow.document.write(`
        <style>
        .sidebar,
                .header{
                    display: none;
                }
                .page-header {
                    display: none;
                }
                .page-wrapper,
                .content {
                    margin: 0 !important;
                    padding: 0 !important;
                }
                </style>
                `);
                
        printWindow.document.write(printContent);
        updateSVGSize();
        printWindow.document.close();

        printWindow.onload = function() {
            // printWindow.print();
            // printWindow.close();
        };
    }


    function createDataPoints(polyline, data, container, imageUrl) {
        const dataPoints = [];
        const numPoints = data.length;
        const initialGapFactor = 0.5;
        let gapFactor = initialGapFactor;

        for (i = 0; i < 3; i++) {
            if (data[i] != null) {

                const x = (i / (numPoints - 1)) * container.clientWidth * 1.5;
                const y = container.clientHeight / 10 + ((data[i] + 1) * 2.5);
                dataPoints.push([x, y]);


                var symbolPoint = document.createElementNS("http://www.w3.org/2000/svg", "image");
                var [symbolX, symbolY] = [x, y];
                symbolPoint.setAttribute("x", symbolX - 5);
                symbolPoint.setAttribute("y", symbolY - 5);
                symbolPoint.setAttribute("width", 10);
                symbolPoint.setAttribute("height", 10);
                symbolPoint.setAttribute("href", imageUrl);
                polyline.parentElement.appendChild(symbolPoint);
            }
        }

        for (let i = 5; i < numPoints + 2; i++) {
            if (data[i - 2] != undefined) {

                const x = ((i / (numPoints - 1)) * container.clientWidth * 1.54) / 2;

                const y = container.clientHeight / 10 + ((data[i - 2] + 1) * 2.2);
                dataPoints.push([x, y]);

                gapFactor -= (initialGapFactor / (numPoints - 4));

                var symbolPoint = document.createElementNS("http://www.w3.org/2000/svg", "image");
                var [symbolX, symbolY] = [x, y];
                symbolPoint.setAttribute("x", symbolX - 5);
                symbolPoint.setAttribute("y", symbolY - 5);
                symbolPoint.setAttribute("width", 10);
                symbolPoint.setAttribute("height", 10);
                symbolPoint.setAttribute("href", imageUrl);
                polyline.parentElement.appendChild(symbolPoint);

                console.log(x, y);
            }
        }

        function pointsToString(dataPoints) {
            return dataPoints.map(point => point.join(",")).join(" ");
        }

        polyline.setAttribute("points", pointsToString(dataPoints));
    }
</script>
</body>

</html>