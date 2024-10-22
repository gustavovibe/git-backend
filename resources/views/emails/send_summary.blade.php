<body>
    <div style="width: 100%; height: 842.88px; position: relative; background: white">
        <div
            style="left: 60.40px; top: 781px; position: absolute;  justify-content: flex-start; align-items: center; gap: 40px; display: inline-flex">
            <table>
                <tr>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/images/globe.png" />
                    </td>
                    <td>
                        <p
                            style="color: #82CF45; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            WIDE SELECTION</p>
                    </td>
                    <td style="width: 10%;"></td>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/images/thumbs-up.png" />
                    </td>
                    <td>
                        <p
                            style="color: #82CF45; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            EASY BOOKING</p>
                    </td>
                    <td style="width: 10%;"></td>
                    <td>
                        <img style="width: 18px; height: 17.54px; left: 3.81px;"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/images/key.png" />
                    </td>
                    <td>
                        <p
                            style="color: #82CF45; font-size: 14px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            SECURE PAYMENTS</p>
                    </td>
                </tr>
            </table>
        </div>
        <div
            style="width: 476px; height: 145px; left: 54.40px; top: 216px; position: absolute; color: #4F5E71; font-size: 45.61px; font-family: Inter; font-weight: 700; word-wrap: break-word">
            {{ $tour['tour_name'] }}</div>
        <div
            style="width: 202px; height: 19px; left: 333.40px; top: 185px; position: absolute; color: #4F5E71; font-size: 18.61px; font-family: Inter; font-weight: 700; text-decoration: underline; word-wrap: break-word">
            Itinerary & Trip Notes</div>
        <img style="width: 226px; height: 68.99px; left: 185.40px; top: 58px; position: absolute"
            src="https://hopeful-nobel.74-208-189-166.plesk.page/images/logo.png" />
        <img style="width: 487px; height: 324.67px; left: 54.40px; top: 407px; position: absolute"
            src="{{ $tour['images'][0] }}" />
    </div>


    <div style="page-break-before: always;">
        <div style="width: 595.92px; height: 900px; position: relative; background: white">
            <div
                style="width: 595.92px; height: 842.88px; padding-bottom: 0.88px; padding-right: 0.44px; left: 0px; top: 0px; position: absolute; flex-direction: column; justify-content: center; align-items: center; display: inline-flex">
                <div style="width: 595.48px; height: 842px; position: relative">
                    <div style="width: 595px; height: 842px; left: 0px; top: 0px; position: absolute">

                        <img style="width: 481.50px; height: 283.50px; left: 57px; top: -680.75px; position: absolute"
                            src="https://hopeful-nobel.74-208-189-166.plesk.page/images/logo.png" />
                    </div>

                </div>
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
            <div style="left: 20%; top: 90%; position: absolute; ">
                <img style="width:80%;height:50%;left: 56.68px; top: 94.83px;"
                    src="https://hopeful-nobel.74-208-189-166.plesk.page/images/pay_methods.jpeg">
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
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/images/logo.png" />
                        </div>
                    </div>
                    <div
                        style="width: 280px; height: 49px; color: #4F5E71; font-size: 10.95px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                        Independent London City Stay</div>
                </div>
            </div>
        </div>
    </div>

    <div style="page-break-before: always;">
        <div>
            <img style="width: 18%; height: 4%; margin-left:6%; "
                                src="https://hopeful-nobel.74-208-189-166.plesk.page/images/logo.png" />
                                <hr style="margin-top:10%;color:#4F5E71">
        </div>

        <div>
            <p style="color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word;">Description</p>
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
            <img style="width:50%;height:10%;left: 56.68px; top: 94.83px;"
                src="https://hopeful-nobel.74-208-189-166.plesk.page/images/pay_methods.jpeg">
        </div>
    </div>



    <div style="page-break-before: always">
        <div style="width: 595.92px; height: 842.88px; position: relative; background: white">
            <div
                style="width: 206.84px; height: 33px; left: 194.56px; top: 781px; position: absolute; justify-content: center; align-items: center; display: inline-flex">
                <img style="width: 206.84px; height: 33px" src="https://via.placeholder.com/207x33" />
            </div>
            <div
                style="width: 297.38px; left: 59.56px; top: 129px; position: absolute; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 20px; display: inline-flex">
                <div
                    style="align-self: stretch; height: 287.85px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 15px; display: flex">
                    <div
                        style="width: 258.25px; height: 34.01px; color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                        What’s Included</div>
                    <div
                        style="height: 238.83px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
                        <div
                            style="align-self: stretch; height: 14.17px; color: #4F5E71; font-size: 13.77px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            Accommodation</div>
                        <div
                            style="align-self: stretch; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                            Superior to Superior First-Class with private bath or shower</div>
                        <div
                            style="align-self: stretch; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                            3 nights in London</div>
                        <div
                            style="align-self: stretch; height: 14.17px; color: #4F5E71; font-size: 13.88px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            Guide</div>
                        <div
                            style="align-self: stretch; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                            The services of a Tour Director is included</div>
                        <div
                            style="align-self: stretch; height: 14.17px; color: #4F5E71; font-size: 13.44px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            Meals</div>
                        <div
                            style="align-self: stretch; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                            Full buffet breakfast daily</div>
                        <div
                            style="align-self: stretch; height: 14.17px; color: #4F5E71; font-size: 13.44px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            Transport</div>
                        <div
                            style="align-self: stretch; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                            Travel by coach or private car</div>
                        <div
                            style="width: 45.62px; height: 14.17px; color: #4F5E71; font-size: 13.44px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                            Others</div>
                        <div
                            style="width: 283.64px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                            Includes hotel taxes, porterage, tips and service charges</div>
                    </div>
                </div>
            </div>
            <div
                style="width: 596px; height: 92px; left: 0px; top: 0px; position: absolute; justify-content: center; align-items: center; display: inline-flex">
                <div
                    style="width: 596px; height: 92px; position: relative; border-bottom: 1px rgba(80, 80, 80, 0.20) solid; flex-direction: column; justify-content: flex-start; align-items: flex-start; display: flex">
                    <div
                        style="width: 595.92px; height: 842.88px; padding-top: 27px; padding-bottom: 766.50px; padding-left: 22.50px; padding-right: 424.92px; flex-direction: column; justify-content: flex-start; align-items: center; display: inline-flex">
                        <div style="width: 148.50px; height: 49.38px; position: relative">
                            <div style="width: 119.25px; height: 19.50px; left: 0px; top: -49.38px; position: absolute">
                            </div>
                            <img style="width: 129px; height: 39.38px; left: 19.50px; top: -39.38px; position: absolute"
                                src="https://via.placeholder.com/129x39" />
                        </div>
                    </div>
                    {{-- <div
                        style="width: 280px; height: 49px; color: #4F5E71; font-size: 10.95px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                        Independent London City Stay</div> --}}
                </div>
            </div>
        </div>
    </div>
    <div style="page-break-before: always">
        <div style="width: 595.92px; height: 842.88px; position: relative; background: white">
            <div
                style="width: 206.84px; height: 33px; left: 194.56px; top: 781px; position: absolute; justify-content: center; align-items: center; display: inline-flex">
                <img style="width: 206.84px; height: 33px" src="https://via.placeholder.com/207x33" />
            </div>
            <div
                style="height: 548.16px; left: 59.56px; top: 129px; position: absolute; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 15px; display: inline-flex">
                <div
                    style="color: #82CF45; font-size: 25px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                    Departure Dates </div>
                <div
                    style="width: 393.46px; justify-content: flex-start; align-items: flex-start; gap: 194px; display: inline-flex">
                    <div
                        style="flex: 1 1 0; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 21px; display: inline-flex">
                        <div
                            style="align-self: stretch; height: 503.16px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 21px; display: flex">
                            <div
                                style="align-self: stretch; justify-content: flex-start; align-items: center; gap: 33px; display: inline-flex">
                                <div
                                    style="justify-content: flex-start; align-items: center; gap: 120px; display: flex">
                                    <div
                                        style="width: 71px; align-self: stretch; color: #4F5E71; font-size: 11.12px; font-family: Inter; font-weight: 700; text-decoration: underline; word-wrap: break-word">
                                        STARTING IN</div>
                                    <div style="width: 42px; height: 42px; position: relative">
                                        <img style="width: 27.12px; height: 23.59px; left: 7.44px; top: 9.22px; position: absolute"
                                            src="https://via.placeholder.com/27x24" />
                                    </div>
                                </div>
                                <div
                                    style="width: 57px; height: 42px; color: #4F5E71; font-size: 11.12px; font-family: Inter; font-weight: 700; text-decoration: underline; word-wrap: break-word">
                                    ENDING IN</div>
                            </div>
                            <div
                                style="width: 393.46px; height: 19.84px; padding-left: 0.44px; padding-right: 0.02px; justify-content: center; align-items: flex-start; gap: 12px; display: inline-flex">
                                <div
                                    style="width: 224px; height: 20px; color: #4F5E71; font-size: 18.56px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    London, UK</div>
                                <div
                                    style="width: 157px; height: 20px; text-align: right; color: #4F5E71; font-size: 18.56px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    London, UK</div>
                            </div>
                            <div
                                style="align-self: stretch; justify-content: space-between; align-items: center; display: inline-flex">
                                <div
                                    style="width: 99.73px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    27 September 2024</div>
                                <div
                                    style="width: 99.73px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    30 September 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.41px; justify-content: center; align-items: flex-start; gap: 223.83px; display: inline-flex">
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.24px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    04 October 2024</div>
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    07 October 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.41px; justify-content: center; align-items: flex-start; gap: 223.83px; display: inline-flex">
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.77px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    11 October 2024</div>
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    14 October 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.41px; justify-content: center; align-items: flex-start; gap: 223.83px; display: inline-flex">
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    18 October 2024</div>
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    21 October 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.41px; justify-content: center; align-items: flex-start; gap: 223.83px; display: inline-flex">
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    25 October 2024</div>
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    28 October 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; justify-content: center; align-items: flex-start; gap: 201.33px; display: inline-flex">
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    01 November 2024</div>
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    04 November 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; justify-content: center; align-items: flex-start; gap: 201.33px; display: inline-flex">
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    08 November 2024</div>
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.77px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    11 November 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; justify-content: center; align-items: flex-start; gap: 201.33px; display: inline-flex">
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    15 November 2024</div>
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    18 November 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; justify-content: center; align-items: flex-start; gap: 201.33px; display: inline-flex">
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    22 November 2024</div>
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    25 November 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.45px; justify-content: center; align-items: flex-start; gap: 200.33px; display: inline-flex">
                                <div
                                    style="width: 96.11px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    29 November 2024</div>
                                <div
                                    style="width: 96.57px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    02 December 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.45px; justify-content: center; align-items: flex-start; gap: 199.87px; display: inline-flex">
                                <div
                                    style="width: 96.57px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    13 December 2024</div>
                                <div
                                    style="width: 96.57px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    16 December 2024</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.21px; justify-content: center; align-items: flex-start; gap: 218.83px; display: inline-flex">
                                <div
                                    style="width: 84.61px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    31 January 2025</div>
                                <div
                                    style="width: 89.81px; height: 11.33px; color: #4F5E71; font-size: 10.42px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    03 February 2025</div>
                            </div>
                            <div
                                style="height: 11.33px; padding-right: 0.21px; justify-content: center; align-items: flex-start; gap: 213.63px; display: inline-flex">
                                <div
                                    style="width: 89.81px; height: 11.33px; color: #4F5E71; font-size: 10.59px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    07 February 2025</div>
                                <div
                                    style="width: 89.81px; height: 11.33px; color: #4F5E71; font-size: 10.77px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    10 February 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                style="width: 596px; height: 92px; left: 0px; top: 0px; position: absolute; justify-content: center; align-items: center; display: inline-flex">
                <div
                    style="width: 596px; height: 92px; position: relative; border-bottom: 1px rgba(80, 80, 80, 0.20) solid; flex-direction: column; justify-content: flex-start; align-items: flex-start; display: flex">
                    <div
                        style="width: 595.92px; height: 842.88px; padding-top: 27px; padding-bottom: 766.50px; padding-left: 22.50px; padding-right: 424.92px; flex-direction: column; justify-content: flex-start; align-items: center; display: inline-flex">
                        <div style="width: 148.50px; height: 49.38px; position: relative">
                            <div
                                style="width: 119.25px; height: 19.50px; left: 0px; top: -49.38px; position: absolute">
                            </div>
                            <img style="width: 129px; height: 39.38px; left: 19.50px; top: -39.38px; position: absolute"
                                src="https://via.placeholder.com/129x39" />
                        </div>
                    </div>
                    <div
                        style="width: 280px; height: 49px; color: #4F5E71; font-size: 10.95px; font-family: Inter; font-weight: 700; word-wrap: break-word">
                        Independent London City Stay</div>
                </div>
            </div>
        </div>
    </div>

</body>
