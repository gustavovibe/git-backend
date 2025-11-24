
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Preview</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <style>
           .h label {
               color: black;
               font-weight: bold;
           }
           .h{
            border-color: black;
            border-style: solid;
            border-width: 1px;
            padding: 2%;
            border-radius: 7px;
           }
           .box {
            display: flex;
            }
            .box img{
                width: 25%;
                height: 80px;
            }
            .box h3{
                margin-left: auto;
                color:#82CF45;
                font-weight:bold;
                margin-top: 25px;
            }
            .column {
             float: left;
            width: 50%;
            }
            .row {
            display: flex;
            }
            .imgP{
                margin-left: 5px;
                margin-right:5px;
                height : 50%;
                width: 50%;
                align-self: center;
            }
            .fTable{
                width: 100%;
                border-collapse: separate;
                border-spacing: 10px 0;
            }
            .fTable th{
                color: #82CF45;
                border-top-color:#82CF45;
                border-top-style: solid;
                text-align: left;
                width: 35%;
            }
            .fTable td{
                font-weight: bold;
            }

            .fTable tfoot td{
                color: #82CF45;
                font-size: 13px;
            }
            .TextB{
                border-style: solid;
                border-width: 1px;
                border-radius: 10px;
                padding: 15px;
                border-color: gray;
            }
            .TextB label{
                font-weight: bold;
            }
            .TextB a{
                color: orange;
                font-weight: bold;
            }
            .Group label{
                color:#82CF45;
                font-size: 12px;
                font-weight: normal;
            }
            .Group p{
                margin-top: 1px;
                font-family: carano;
                font-weight: bold;
                color: rgb(116, 116, 116);
            }
            .pTable{
                width: 100%;
                border-collapse:collapse;
                margin: 0 auto;
            }
            .pTable th{
                color:#82CF45;
                border-bottom-color:#82CF45;
                border-bottom-style: solid;
                padding-bottom: 10px;
                font-size: 15px;
            }
            .pTable tbody td{
                font-weight: bold;
                border-bottom-color:#82CF45;
                border-bottom-style: solid;
                padding-top: 10px;
                padding-bottom: 10px;
                font-size: 15px;
            }
            .pTable tfoot  td:nth-child(-n+3){
                color:#82CF45;
                font-weight: bold;
                padding-top: 10px;
            }
            .pTable tfoot tr:last-child td:nth-child(-n+4){
                color:#82CF45;
                font-weight: bold;
                padding-top: 10px;
            }
            .pTable th:nth-child(-n+2),
            .pTable td:nth-child(-n+2) {
                text-align: left;
            }

            .pTable th:last-child,
            .pTable td:last-child {
                text-align: right;
            }
            .descG{
                color:#82CF45;
            }
            .descG hr{
                border: none;
            height: 2px;
            background-color: #82CF45;
            }
            .dGroup{
                background-color: #82CF45;
                color: white;
                padding: 3%;
                border-radius: 10px;
                width: 90%;
                margin: 0 auto;
            }
            .dGroup img{
                border-radius: 10px;
                width: 100%;
            }
            .column img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        .pDetails {
            padding: 5%;
            background-color: rgba(0, 128, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 0 auto;
        }
        .pDetails label{
            color: #82CF45;
            font-size: 15px;
        }
        .pDetails p{
            font-size: 15px;
        }
        .mh{
                margin: 0 20px;
            }
            @media (max-width: 768px) {
            .mh {
            margin: 0 10px;
            }
            }
            @media (max-width: 480px) {
            .mh {
            margin: 0 5px;
            }
            }
        </style>
    </head>
<body style=" font-family: 'Canaro', sans-serif;">
   {{--  <div >
        <div class="h mh" >
            <label>Email preview for the last order 149774</label>
        </div>
    </div> --}}
    <br>
    <div class="box mh">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png')))}}">
        <h3>Confirmed Order</h3>
    </div>
    <br>
    <div class="mh">
        <img class="imgP" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/template_vibe.jpg')))}}" alt="Girl in a jacket" width="100%" height="auto">
    </div>
    <br>
    <br>
    <div class="mh">
        <table class="fTable" >
            <tr >
                <th>Order Number</th>
                <th>Order Total</th>
                <th>Balance</th>
            </tr>
            <tr>
                <td>149-774</td>
                <td>USD1,672.00</td>
                <td>USD0.00</td>
            </tr>
            <tfoot>
                <tr>
                    <td>Created on: Dec 13,2023</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <br>
    <div class="TextB mh">
        <div class="row">
            <div class="column">
                <label>Thanks for booking with Project Expedition</label>
                <p>If you have any questions or request, please reply to this message.</p>
                <p>PS: Wanna travel more? Then get another adeventure with 7.5% off "Thanks" promocode on our  <a>webpage.</a> </p>
                <p><b>Best regards,</b></p>
                <label>Project Expedition's Team</label>
                <br>
                <a href="https://vibeadventures.com">https://vibeadventures.com</a>
                <p>Tel.1:(+52) 55-8526-6910 (Mexico)</p>
                <p>Tel.2:(+1) 20-1500-1310 (USA)</p>
                <p>Tel.3:(+44) 74-4096-3840 (UK)</p>
            </div>
            <div class="column" style="width: 5%">

            </div>
            <div class="column Group">
                <div>
                    <label>Name</label>
                    <p>Michael Malony</p>
                </div>
                <div>
                    <label>Email</label>
                    <p>support+PE70510412@projectexpedition.com</p>
                </div>
                <div>
                    <label>Phone</label>
                    <p>+1-479-426-0255</p>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="mh">
      <h3>Special Requirements</h3>
      <p>Hotel pick-up Excellence Playa Mujeres</p>
      <p>Rejected booking fields</p>
      <p>Custom Questions: null</p>
      <hr>
    </div>
    <br>
    <div class="mh">
        <h3>Payment History</h3>
        <table class="pTable">
            <tbody>
                <tr>
                    <th>Payment History</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Credit card</td>
                    <td>Dec 13, 2023</td>
                    <td>USD 1,672.00</td>
                </tr>
            </tbody>
            <tfoot>
                <td></td>
                <td>Total</td>
                <td>USD 1,672.00</td>
            </tfoot>
        </table>
    </div>
    <br>
    <div class="row mh">
        <div class="column descG" style="width: 70%;">
            <h3>Chichen Itza Tour: Pyramisd,Cenote Ik & Valladolid (Private / 12h)</h3>
            <hr>
            <p>Saturday,December 30,2023 8:00 AM</p>
        </div>
        <div class="column" style="width: 10%">

        </div>
        <div class="column" style="width: 20%;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/qr.png')))}}">
        </div>
    </div>
    <br>
    <div class="dGroup">
        <div class="row">
            <div class="column"  style="width: 30%">
                <Label>Starts</Label>
                <p>08:00</p>
                <p>30 December 2023</p>
            </div>
            <div class="column"  style="width: 30%">
                <Label>Address</Label>
                <p>Mexico: Chichen Itza, Cenote Ik Kil & Valladolid Mexico</p>
                <a>Get Direccions</a>
            </div>
            <div class="column" style="width: 10%">

            </div>
            <div class="column"  style="width: 30%">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/city.png')))}}">
            </div>
        </div>
    </div>
    <br>
    <div class="mh">
        <h3>Item Summary</h3>
        <table class="pTable">
            <tr>
                <th>#</th>
                <th>Description</th>
                <th></th>
                <th>Total</th>
            </tr>
            <tr>
                <td>8</td>
                <td>Group</td>
                <td>USD 209.00</td>
                <td>USD 1,672.00</td>
            </tr>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td>VAT(inclusive)</td>
                    <td>USD 1,672.00</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>Total</td>
                    <td>USD 1,672.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
<br>
    <div class="mh">
        <h3>Participant Details</h3>
        <div class="pDetails">
            <div>
                <h4>Participant 1</h4>
                <label>First Name</label>
                <p>Michael</p>
                <label>Last Name</label>
                <p>Malony</p>
            </div>
            <div>
                <h4>Participant 1</h4>
                <label>First Name</label>
                <p>Michael</p>
                <label>Last Name</label>
                <p>Malony</p>
            </div>
            <div>
                <h4>Participant 1</h4>
                <label>First Name</label>
                <p>Michael</p>
                <label>Last Name</label>
                <p>Malony</p>
            </div>
            <div>
                <h4>Participant 1</h4>
                <label>First Name</label>
                <p>Michael</p>
                <label>Last Name</label>
                <p>Malony</p>
            </div>
        </div>
    </div>
</body>
</html>


