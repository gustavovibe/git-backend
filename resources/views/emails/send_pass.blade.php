<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Document</title>
    <style>
        .column {
            float: left;
            width: 50%;
        }

        .row {
            display: flex;
        }

        .btnT {
            display: flex;
            justify-content: space-between;

            text-align: right;
            margin-top: 40px;
            align-items: center;
        }

        .btnT label {
            background-color: orange;
            color: white;
            border-color: orange;
            border-radius: 5px;
            font-weight: bold;
            padding: 10px;
        }

        .btnT u {
            font-weight: bold;
            text-underline-position: below;

        }

        .btnT img {
            width: 70%;
            float: left;
        }

        .t1 {
            margin: 0 20px;
            align-content: center;
            text-align: center;
            margin-top: 20px;
        }

        .t1 label {
            /*   border-style: solid; */
            padding: 3%;
            border-radius: 15px;
            background-color: rgba(0, 128, 0, 0.1);
        }

        .textG #color {
            color: #82CF45;
        }

        .textG #under {
            color: #82CF45;
            text-decoration: underline;
        }


        .tGroup {
            display: flex;
            justify-content: space-between;
            margin: 0 20px;
            text-align: right;
            align-items: center;
        }

        .tGroup u {
            color: orange;
            font-weight: bold;
        }

        .Borderg {
            border-color: #82CF45;
            border-style: solid;
            border-radius: 15px;

        }

        .tDesc img {
            padding: 8%;
            border-radius: 15px;
            display: block;
            margin: 0 auto;
            width: 80%;
            height: 80%;
        }

        .tDesc b {
            border-style: solid;
            border-radius: 5px;
            border-color: #82CF45;
            border-width: 2px;
            padding: 1%;
        }

        .fGroup {
            padding: 1%;
            display: flex;
            flex-direction: row;
            margin-bottom: 20px;
        }

        .fGroup #t2 {
            /* text-align: center; */
            align-content: center;
            color: orange;
            border-style: solid;
            padding: 1%;
            display: inline-block;
            margin-top: 1%;
            border-radius: 8px;
            font-weight: bold;
        }

        .ffGroup {
            display: flex;
            justify-content: space-between;
            text-align: right;
            align-items: center;
        }

        .ffGroup a {
            color: gray;
            font-weight: bold;
        }

        .ffGroup label {
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .fffgroup {
            border-width: 1px;
            border-style: solid;
            border-radius: 10px;
            border-color: gainsboro;
            border-width: 2px;

        }

        .fffgroup .column:first-child {
            text-align: right;
            width: 20%;
        }

        .fffgroup .column:last-child {
            text-align: right;
            align-content: center;
        }

        .fffgroup label {
            font-weight: bold;
        }

        .Tcolor {
            color: #82CF45;
        }

        .Tscolor {
            color: grey;
        }

        .png {
            vertical-align: middle;
            margin: 0 5px;

        }

        .Tbox {
            border-style: solid;
            border-radius: 5px;
            border-color: #82CF45;
            border-width: 2px;
            color: #82CF45;
            margin-bottom: 1em;
            display: inline-block;
        }

        .mh {
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

        .t1 {
            width: 100%;
            border-collapse: collapse;
            margin-left: 1px;
        }

        .t1 td:first-child {
            text-align: left;
        }

        .t1 td:last-child {
            text-align: right;
        }

        .t1 a {
            color: #82CF45;
        }

        .t1 tbody td {
            border-bottom: gainsboro;
            border-bottom-style: solid;
            padding: 1%;
        }

        .t1 tfoot td {
            color: #82CF45;
            font-weight: bold;
            padding-right: 1%;
        }


        .tt {

            justify-content: center;
            align-items: center;
            text-align: center;
            margin: 20px;

        }

        .tt label {
            width: 100%;
            border: 2px solid #82CF45;
            padding: 3%;
            border-radius: 15px;
            background-color: rgba(0, 128, 0, 0.1);
            font-size: 1.5rem;
            max-width: 100%;
        }


        @media (max-width: 768px) {
            .tt label {
                font-size: 1.2rem;
                padding: 5%;
            }

            .tt {
                margin: 10px;
            }
        }


        @media (max-width: 480px) {
            .tt label {
                font-size: 1rem;
                padding: 6%;
            }

            .tt {
                margin: 5px;
            }
        }


        .laterald {
            display: flex;
            justify-content: space-between;
        }

        .grid-container {
            display: grid;
            padding: 1%;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .grid-container div label {
            color: #82CF45;
        }

        .grid-container div h4 {
            text-decoration: underline;
        }

        .Recomend #container {
            width: 100%;
            margin-left: -10px;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 3%;
            background-color: rgba(0, 128, 0, 0.1);
        }

        .Recomend h1 {
            text-align: center;
        }

        .Recomend p {
            text-align: center;
        }



        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .card {
            width: 100%;
        }


        .card #img_ {
            width: 80%;
            height: 100px;
            /* padding: 5%; */
            border-radius: 10px
        }

        .card #iconic {
            margin-top: 2%;
            width: 10%;
        }

        .card td {
            width: 20%;
            padding: 1%
        }



        .card a {
            background-color: orange;
            color: white;
            /*   padding: 1%; */
            padding-top: 2%;
            font-weight: bold;
            border-radius: 5px;
            font-size: 11px;
            display: block;
            text-align: center;
            align-content: center;
            vertical-align: middle;
            height: 3%;
            margin-bottom: 3%;
            margin-left: 3%;
            margin-right: 3%;
        }

        .card p {
            font-size: 12px;
            text-align: center;
            height: 7%;
            color: gray;
        }

        .card h5 {
            text-align: center;
        }

        .card div {
            background-color: white;
            padding: 2%;
            border-radius: 12px;
        }

        .footer hr {
            color: #ddd;
            margin-bottom: 3%;
            margin-top: 3%;
        }

        .footer label {
            color: gray;
        }

        .footer p {
            color: gray;
        }

        .footer a {
            text-decoration: underline;
        }

        .line-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            height: 200px;
            /* Adjust height as needed */
            margin: 20px 0;
        }

        .line {
            width: 2px;
            height: 100%;
            margin-left: 50%;
            background-color: gainsboro;
            position: relative;
        }

        .line::before,
        .line::after {
            content: '';
            width: 10px;
            height: 10px;
            background-color: #ddd;
            border-radius: 50%;
            position: absolute;
            left: 80%;
            transform: translateX(-50%);
        }

        .line::before {
            top: 0;
        }

        .line::after {
            bottom: 0;
        }

        .airplane-icon {
            position: absolute;
            top: 50%;
            left: 48.5%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: auto;
        }

        .icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-70%, -70%) rotate(180deg);
            /* Rotate the icon to point down */
            font-size: 24px;
        }
    </style>
</head>

<body style="font-family: 'Canaro', sans-serif; padding:2%">
    <br>
    <div>
        <div class="lateralD btnT mh">
            <div>
                <img style="width: 50%; height:50%"  src="https://vibeadventures.be/images/logo.png" alt="">
            </div>
            <div>
                <label style="margin-top: 20px;">Hi {{ $data['name'] }}</label>
                <P style="text-align: right;">For more info, open <u>Help & support</u> </P>
            </div>
        </div>
    </div>
    <br>

    <br>
    <div class="textG mh" style="text-align: justify;">
        <h1>Dear <a class="Tcolor">{{ $data['name'] }}!</a></h1>
        <p>We hope this message finds you well. You have been issued a temporary password to access your account on our system.</p>
        <div style="text-align: center; margin: 20px;">
            <label
                style="display: block; width: 100%; max-width: 100%; border: 2px solid #82CF45; padding: 3%; border-radius: 15px; background-color: rgba(0, 128, 0, 0.1); font-size: 1.2rem; color: #82CF45;">
                Temporary Password for Your Account:
                <b>{{' '.$data['password'] }}</b>
            </label>
        </div>
        <p>Please use this password to log in and make sure to update it immediately after logging in for security reasons.</p>

        <p>Important: To reset your password, go to the account settings page and follow the instructions.</p>

        <p style="font-style: italic;">
            If you encounter any issues or need further assistance, feel free to contact our support team.

            Thank you for your cooperation, and we look forward to serving you!
        </p>
    </div>
        <div style="text-align: center;">
            <h3 style="color: orange;text-decoration: underline;"><a href="{{ rtrim(config('frontend.url'), '/') }}/">Change my password</a>   </h3>
        </div>

    <br>

    <div style="padding: 2%">
        <br>
        <div style="page-break-before: always;" class="Recomend">
            <div id="container">
                <h2 style="text-align: center;">Recommended</h2>
                <p style="color: grey; font-size:12px;">Adding these services to your trip now can save you money to
                    purchasing them later or in the
                    destination</p>
                <div>
                    <table class="card">
                        <tr>
                            <td>
                                <div>
                                    <img id="img_" src="https://vibeadventures.be/images/transfer.png">
                                    <h5>Airport transfer</h5>
                                    <p style="width:100%">Airport transfers not included adventure?</p>
                                    <a>Go somewhere <img id="iconic"
                                            src="https://vibeadventures.be/images/box-arrow-up-right.png"> </a>

                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="https://vibeadventures.be/images/insurance.png">
                                    <h5>Insurance</h5>
                                    <p style="width:100%">Available up to 24h before departure</p>
                                    <a>Manager Insurance <img id="iconic"
                                            src="https://vibeadventures.be/images/box-arrow-up-right.png"></a>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="https://vibeadventures.be/images/accommodation.png">
                                    <h5>Accommodation</h5>
                                    <p style="width:100%">Need pre- or post-tour accommodation?</p>
                                    <a>Book Accommodation <img id="iconic"
                                            src="https://vibeadventures.be/images/box-arrow-up-right.png"></a>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="https://vibeadventures.be/images/activities.png">
                                    <h5>Activities</h5>
                                    <p style="width:100%">Got extra days in the destination before or after
                                        the adventure?</p>
                                    <a>Find Activities <img id="iconic"
                                            src="https://vibeadventures.be/images/box-arrow-up-right.png"></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <div class="footer">
            <hr>
            <div>
                <table width="100%">
                    <tr>
                        <label style="font-size: 18px;">
                            Excellent
                            <img src="https://vibeadventures.be/images/ranking.png'))) }}" alt=""
                                style="vertical-align: middle;">
                        </label>
                        <td style="text-align: right;">
                            <img src="https://vibeadventures.be/images/trust-index.png'))) }}" alt="">
                        </td>
                    </tr>
                </table>
            </div>
            <hr>
            <br>
            <div>
                <table width="100%">
                    <tr>
                        <img style="width:35%;" src="https://vibeadventures.be/images/logo.png'))) }}">
                        <td style="text-align: right;">
                            <img src="https://vibeadventures.be/images/face-icon.png'))) }}">
                            <img src="https://vibeadventures.be/images/insta-icon.png'))) }}">
                            <img src="https://vibeadventures.be/images/youtube-icon.png'))) }}">
                        </td>
                    </tr>
                </table>
                <br>
                <div>
                    <p>300 Delaware Ave, Ste 210 #549</p>
                    <p>Wilmington, DE 19801</p>
                    <br>
                    <p>You can <a>change your email preferences</a> or view our <a>Terms & Conditions</a> and <a>Privacy
                            Policy</a></p>
                </div>
            </div>
        </div>
</body>


</html>
