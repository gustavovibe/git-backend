<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adventure Itinerary</title>
    <style>
        @page {
            margin: 20mm 10mm 30mm 10mm; /* add bottom margin to allow space for footer */
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: auto;
            z-index: 1000;
        }
        div.cover .footer { display: none; }
    </style>
</head>
<body style="margin: 10px;padding: 0;font-family: Arial, sans-serif;">
  <div>  
    <div class="cover" style="width:100%">
        <table style="text-align:center;width:100%">
            <tr style="margin-bottom:100px">
                <td>
                    <img style="width: 400px; height: auto;" src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                </td>
            </tr>
            <tr style="margin-bottom:100px">
                <td>
                <span style="color: #4F5E71; font-size: 22px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                Adventure Itinerary
                </span>
                </td>
            </tr>
            </tr>
            <tr style="margin-bottom:100px">
                <td>
                    <span style="color: #4F5E71; font-size: 60px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                        {{ $tour['tour_name'] }}
                    </span>
                </td>
            </tr>
            <tr style="margin-bottom:100px">
                <td>
                    <img style="width: 600px; height: auto;"src="{{ $tour['images'][0] }}" />                
                </td>
            </tr>
        </table>
        <table style="text-align:center;width:100%">
                <tr>
                    <td>
                        <img style="width: 18px; height: 17.54px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/globe.png" />
                        <span
                            style="color: #82CF45; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                            WIDE SELECTION</span>
                    </td>
                    <td style="width: 10%;"></td>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/thumbs-up.png" />
                            <span
                            style="color: #82CF45; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                            EASY BOOKING</span>
                    </td>
                    <td style="width: 10%;"></td>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/key.png" />
                            <span
                            style="color: #82CF45; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                            SECURE PAYMENTS</span>
                    </td>
                </tr>
        </table>
    </div>
    <div style="page-break-before: always;width:100%">
        <table style="width:100%; position: relative; background: white;text-align:center">
            <tr>
                <td style="text-align:left">
                    <img style="width: 226px; height: 68.99px;"
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                    </td>
                    <td style="text-align:right">
                    <span style="color: #4F5E71; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                        {{ $tour['tour_name'] }}
                    </span>
                </td>
            </tr>
        </table>
        <table style="text-align:center">
            <tr>
                <td>
                <span style="color: #82CF45; font-size: 25px; font-family: Inter; word-wrap: break-word; font-weight: 700; ;">
                    Overview
                </span><br>
                <img style="width:100%; height:auto" src="{{ $tour['map'] }}">
                </td>
            </tr>
        </table>
        <table style="margin:10px 0;width:100%;">
            <tr>
                <td style="background-color: rgba(130, 207, 69, 0.20);width:70%">
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        DURATION
                    </span><br>
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                    {{ $tour['tour_length_days'] }} days
                    </span>
                </td>
                <td style="width:2%">
                </td>
                <td style="background-color: rgba(130, 207, 69, 0.20);width:28%">
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        MAX GROUP SIZE
                    </span><br>
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                    {{ $tour['max_group_size'] }}
                    </span>
                </td>
            </tr>
        </table>
        <table style="margin:10px 0;background-color: rgba(130, 207, 69, 0.20);">
            <tr>
                <td style="width:15%">
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                       VISITED <br> COUNTRIES
                    </span>
                </td>
                <td style="width:1%"></td>
                <td style="width:33%;" v-align="middle">
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                    {{ $countries_d['countries_text'] }}
                    </span>
                </td>
                <td style="width:2%"></td>
                <td style="width:15%">
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                       STARTS IN <br> ENDS IN
                    </span>
                </td>
                <td style="width:1%"></td>
                <td style="width:33%">
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        {{ $tour['start_city']['city_name'] }} <br>
                        {{ $tour['end_city']['city_name'] }}
                    </span>
                </td>
            </tr>
        </table>
        <table style="margin:10px 0;background-color: rgba(130, 207, 69, 0.20);">
            <tr>
                <td>
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        ADVENTURE STYLES
                    </span><br>
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        {{ $countries_d['tour_text'] }}
                    </span>
                </td>
            </tr>
        </table>
        <table style="margin:10px 0;">
            <tr>
                <td style="background-color: rgba(130, 207, 69, 0.20);width:48%">
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        OPERATED IN
                    </span><br>
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        {{ $countries_d['guide_text'] }}
                    </span>
                </td>
                <td style="width:2%">
                </td>
                <td style="background-color: rgba(130, 207, 69, 0.20);width:48%">
                    <span style="color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        PHYSICALLY DIFFICULTY
                    </span><br>
                    <span style="color: gray; font-size:14px; font-family: Inter; word-wrap: break-word; font-weight: 700;">
                        Moderate
                    </span>
                </td>
            </tr>
        </table>
    </div>
    <div style="page-break-before: always;width:100%">
            <table style="width:100%; position: relative; background: white;text-align:center">
                <tr>
                    <td style="text-align:left">
                    <img style="width: 226px; height: 68.99px;"
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                    </td>
                    <td style="text-align:right">
                        <span style="color: #4F5E71; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                            {{ $tour['tour_name'] }}
                        </span>
                    </td>
                </tr>
            </table>

        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; word-wrap: break-word; font-weight: 700; ;">Introduction</p>
        </div>
        <div style="text-align: justify; color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 400; margin-top:3%;">
            {!! $tour['overview'] !!}
        </div>
        <br>
        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; word-wrap: break-word; font-weight: 700; ;">Itinerary</p>
        </div>

        <div>
            @foreach ( $tour['itinerary'] as $day )
                <div style="page-break-inside: avoid;">
                    <label style="text-align: justify; color: #4F5E71; font-size: 20px; font-family: Inter; word-wrap: break-word; font-weight: 400; margin-top:3%;">{!! $day['title'] !!}</label>
                    <div style="text-align: justify; color: #4F5E71; font-size:16px; font-family: Inter; word-wrap: break-word; font-weight: 400; margin-top:3%;">{!! $day['description'] !!}</div>
                </div>
                <br>
            @endforeach
        </div>
    </div>
    <div style="page-break-before: always;width:100%">
        <table style="width:100%; position: relative; background: white;text-align:center">
                <tr>
                    <td style="text-align:left">
                    <img style="width: 226px; height: 68.99px;"
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                    </td>
                    <td style="text-align:right">
                        <span style="color: #4F5E71; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                            {{ $tour['tour_name'] }}
                        </span>
                    </td>
                </tr>
        </table>
        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; word-wrap: break-word; font-weight: 700; ;">Whats included!</p>
        </div>
        @foreach ( $services as $key=>$value)
        <div style="page-break-inside: avoid;">
          <h3>{{ ucfirst($key) }}</h3>
            @foreach ( $value as $item )
                <label  style="text-align: justify; color: #4F5E71; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 400; margin-top:3%;">{!! $item['description'] !!}</label>
            @endforeach
        </div>
        @endforeach

        @foreach ( $services as $key=>$value)
        <div style="page-break-inside: avoid;">
          <h3>{{ ucfirst($key) }}</h3>
            @foreach ( $value as $item )
                <label  style="text-align: justify; color: #4F5E71; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 400; margin-top:3%;">{!! $item['description'] !!}</label>
            @endforeach
        </div>
        @endforeach
    </div>
    <div style="page-break-before: always;width:100%">
        <table style="width:100%; position: relative; background: white;text-align:center">
                <tr>
                    <td style="text-align:left">
                    <img style="width: 226px; height: 68.99px;"
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/public/images/logo.png" />
                    </td>
                    <td style="text-align:right">
                        <span style="color: #4F5E71; font-size: 14px; font-family: Inter; word-wrap: break-word; font-weight: 700; ">
                            {{ $tour['tour_name'] }}
                        </span>
                    </td>
                </tr>
        </table>
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
                <h1 style=" font-family: Inter; word-wrap: break-word; ">Expert Customer Support</h1>
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
    </div>
  </div>
  <img class="footer" src="https://vibeadventures.be/images/Footer.png" />
</body>