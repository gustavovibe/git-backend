<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Summary</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .footer img {
            width: 100%;
            height: 60px;
        }
    </style>
</head>
<body style=" font-family: sans-serif;">
<div>  
        <table style="text-align:center">
            <tr>
                <td>
                    <img style="width: 226px; height: 68.99px;"src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                </td>
            </tr>
            <tr>
                <td>
                <span style="color: #4F5E71; font-size: 18.61px; font-family: Inter; font-weight: 700; text-decoration: underline; word-wrap: break-word">
                Adventure Itinerary
                </span>
                </td>
            </tr>
            </tr>
            <tr>
                <td>
                    <span style="color: #4F5E71; font-size: 45.61px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                    {{ $tour['tour_name'] }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <img style="width: 487px; height: 324.67px;"src="{{ $tour['images'][0] }}" />                
                </td>
            </tr>
        </table>
        <table style="text-align:center">
                <tr>
                    <td>
                        <img style="width: 18px; height: 17.54px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/globe.png" />
                        <span
                            style="color: #82CF45; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            WIDE SELECTION</span>
                    </td>
                    <td style="width: 10%;"></td>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/thumbs-up.png" />
                            <span
                            style="color: #82CF45; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            EASY BOOKING</span>
                    </td>
                    <td style="width: 10%;"></td>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/key.png" />
                            <span
                            style="color: #82CF45; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            SECURE PAYMENTS</span>
                    </td>
                </tr>
        </table>
    </div>


    <div style="page-break-before: always;">
        <table style="width:100%; height: 900px; position: relative; background: white;text-align:center">
            <tr>
                <td>
                <img style="width: 481.50px; height: 283.50px; left: 57px; top: -680.75px; position: absolute"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                </td>
                <td>
                <span style="color: #4F5E71; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                    {{ $tour['tour_name'] }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 45%; left: 55px; top: 480px; position: absolute;">
                <!-- Líneas con duración -->
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    DURATION
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $tour['tour_length_days'] }} days (+ days of flights)
                </div>
            </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 35%; left: 372.48px; top: 480px; position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    MAX GROUP SIZE
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $tour['max_group_size'] }}
                </div>
            </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 35%;left: 55px; top: 556px; position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    VISITED COUNTRIES
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $countries_d['countries_text'] }}

                </div>
            </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 20%;  left: 300px; top: 556px; position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    STARTS IN:
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $tour['start_city']['city_name'] }}
                </div>
            </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 20%;left: 460px; top: 556px; position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    ENDS IN:
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $tour['end_city']['city_name'] }}
                </div>
            </div>
            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 88%; left: 55px; top: 624px;  position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    ADVENTURE STYLES
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $countries_d['tour_text'] }}
                </div>
            </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 40%;  left: 55px; top: 700px;  position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    OPERATED IN
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ $countries_d['guide_text'] }}
                </div>
            </div>

            <div
                style="background-color: rgba(130, 207, 69, 0.20); border-radius:6px; padding: 10px;width: 40%;  left: 340px; top: 700px;  position: absolute;">
                <div style="color: #4F5E71; font-size: 11.30px; font-family: Inter; font-weight: 700;">
                    ADVENTURE CODE
                </div>
                <div style="color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; margin-top:3%;">
                    {{ '#' . $tour['tour_id'] }}
                </div>
            </div>

            <div style="left: 56.68px; top: 20%; position: absolute; ">
                <img style="width:100%;height:40%;left: 56.68px; top: 94.83px;" src="{{ $tour['map'] }}">
            </div>

            <div
                style="width: 222.24px; height: 34.01px; left: 56.68px; top: 94.83px; position: absolute; color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                Overview</div>
            <div
                style="width: 596px; height: 92px; left: -0.08px; top: 0px; position: absolute; justify-content: center; align-items: center; display: inline-flex">
                <div
                    style="width: 596px; height: 92px; position: relative; border-bottom: 1px rgba(80, 80, 80, 0.20) solid; flex-direction: column; justify-content: flex-start; align-items: flex-start; display: flex">
                    <div
                        style="width: 595.92px; height: 842.88px; padding-top: 27px; padding-bottom: 766.50px; padding-left: 22.50px; padding-right: 424.92px; flex-direction: column; justify-content: flex-start; align-items: center; display: inline-flex">
                        <div style="width: 148.50px; height: 49.38px; position: relative">
                            <div style="width: 119.25px; height: 19.50px; left: 0px; top: -49.38px; position: absolute">
                            </div>
                            <img style="width: 129px; height: 39.38px; left: 19.50px; top: -39.38px; position: absolute"
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top: auto; text-align: center; ">
           {{--   <div style="margin-top: auto; text-align: center; page-break-inside: avoid;">
            <img style="width:100%;height:60px;left: 56.68px; top: 94.83px;"
            src="https://vibeadventures.be/images/Footer.png" >
        </div> --}}
        </div>
    </div>

    <div style="page-break-before: always;">
            <table style="width:100%; height: 900px; position: relative; background: white;text-align:center">
                <tr>
                    <td>
                    <img style="width: 481.50px; height: 283.50px; left: 57px; top: -680.75px; position: absolute"
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                    </td>
                    <td>
                    <span style="color: #4F5E71; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                        {{ $tour['tour_name'] }}
                        </span>
                    </td>
                </tr>
            </table>

        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word;">Introduction</p>
        </div>
        <div style="text-align: justify; color: #4F5E71; font-size: 11px; font-family: Inter; font-weight: 400; margin-top:3%;">
            {!! $tour['overview'] !!}
        </div>
        <br>
        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word;">Itinerary</p>
        </div>

        <div>
            @foreach ( $tour['itinerary'] as $tour )
                <div style="page-break-inside: avoid;">
                    <label style="text-align: justify; color: #4F5E71; font-size: 20px; font-family: Inter; font-weight: 400; margin-top:3%;">{!! $tour['title'] !!}</label>
                    <div style="text-align: justify; color: #4F5E71; font-size: 11px; font-family: Inter; font-weight: 400; margin-top:3%;">{!! $tour['description'] !!}</div>
                </div>
                <br>
            @endforeach
        </div>
        <br style="margin-top: 5%;">
        <div style="margin-top: auto; text-align: center; page-break-inside: avoid;">
           {{--   <div style="margin-top: auto; text-align: center; page-break-inside: avoid;">
            <img style="width:100%;height:60px;left: 56.68px; top: 94.83px;"
            src="https://vibeadventures.be/images/Footer.png" >
        </div> --}}
        </div>
    </div>



    <div style="page-break-before: always">
        <div>
            <img style="width: 18%; height: 4%; margin-left:6%; "
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                                <hr style="margin-top:10%;color:#4F5E71">
        </div>
        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word;">Whats included!</p>
        </div>
        @foreach ( $services as $key=>$value)
        <div style="page-break-inside: avoid;">
          <h3>{{ ucfirst($key) }}</h3>
            @foreach ( $value as $item )
                <label  style="text-align: justify; color: #4F5E71; font-size: 14px; font-family: Inter; font-weight: 400; margin-top:3%;">{!! $item['description'] !!}</label>
            @endforeach
        </div>
          @endforeach
        @foreach ( $services as $key=>$value)
        <div style="page-break-inside: avoid;">
          <h3>{{ ucfirst($key) }}</h3>
            @foreach ( $value as $item )
                <label  style="text-align: justify; color: #4F5E71; font-size: 14px; font-family: Inter; font-weight: 400; margin-top:3%;">{!! $item['description'] !!}</label>
            @endforeach
        </div>
          @endforeach

          <br>
          <div style="margin-top: auto; text-align: center; page-break-inside: avoid;">
           {{--   <div style="margin-top: auto; text-align: center; page-break-inside: avoid;">
            <img style="width:100%;height:60px;left: 56.68px; top: 94.83px;"
            src="https://vibeadventures.be/images/Footer.png" >
        </div> --}}
        </div>
    </div>


    <div style="page-break-before: always">
        <div>
            <img style="width: 18%; height: 4%; margin-left:6%; "
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                                <hr style="margin-top:10%;color:#4F5E71">
        </div>
        <div>
            <h2 style="color: #82CF45;">Why Book With us?</h1>
            <table>
                <tr style="height: 30%; ">
                    <td style="width: 50%;" >
                        <div style="padding:2%">
                            <img  style="width: 20%; height: auto;"  src="https://vibeadventures.be/images/verified.png" alt="">
                            <h3 style="color: #82CF45;">Top tours</h3>
                            <p style="text-align: justify;color: #4F5E71;">Our thorough screening process ensures you'll choose from the finest adventures and travel packages worlwide</p>
                        </div>
                    </td>



                    <td style="width: 50%;">
                        <div style="padding: 2%;">
                            <img style="width: 20%; height: auto;" src="https://vibeadventures.be/images/lock.png"  alt="">
                            <h3 style="color: #82CF45;">Secure payments</h3>
                            <p style="text-align: justify;color: #4F5E71;">Enjoy secure and flexible payment options, including 'Book Now, Pay Later,' allowing you to plan your dream trip with ease and peace of mind.</p>
                        </div>
                    </td>
                </tr>

            </table>
            <br>
            <table>
                <tr style="height: 30%">
                    <td  style="width: 50%">
                        <div style="padding: 2%;">
                            <img style="width: 20%; height: auto;" style="width: 20%; height: auto;"  src="https://vibeadventures.be/images/globe2.svg"  alt="">
                            <h3 style="color: #82CF45;">Wide selection</h3>
                            <p style="text-align: justify;color: #4F5E71;">Choose from over 20,000 adventures and book flights from almost any airport in the world, making it easy to find your ideal trip!</p>
                        </div>
                    </td>

                    <td style="width: 50%">
                        <div style="padding: 2%;">
                            <img style="width: 20%; height: auto;" src="https://vibeadventures.be/images/hand-thumbs-up.svg"  alt="">
                            <h3 style="color: #82CF45;">Easy booking</h3>
                            <p style="text-align: justify;color: #4F5E71;">Book everything you need in one place—flights, accommodations, activities, and more—with just a few clicks.</p>
                        </div>
                    </td>
                </tr>
            </table>
            <br>
            <div style="text-align: center;">
                <h1 style=" font-family: Inter; ">Expert Customer Support</h1>
            </div>
            <table style="width: 100%">
                <tr>
                    <td style="width:30%; text-align:center;">
                        <img style="width: 20%; height: auto;" src="https://vibeadventures.be/images/people.svg"  alt="">
                    </td>
                    <td>
                        <p style="text-align: justify;color: #4F5E71;">Our knowledgeable team of travel experts has explored countless destinations worldwide and is ready to help you book your dream vacation. With a wealth of industry experience and a commitment to exceptional service, we’re here to assist you with every aspect of your journey.</p>
                    </td>
                </tr>
            </table>
        </div>
        <br>
      {{--   <div style="margin-top: auto; text-align: center; page-break-inside: avoid;">
            <img style="width:100%;height:60px;left: 56.68px; top: 94.83px;"
            src="https://vibeadventures.be/images/Footer.png" >
        </div> --}}
    </div>

</body>
