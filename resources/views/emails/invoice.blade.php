<body>
    <div style="padding: 15%">

        <table style="width: 100%">
            <tr>
                <td>
                    <div>
                        <h2 style="color: #7F91A8">INVOICE</h2>
                        <h2 style="color: #7F91A8">{{ $data['charge_details']['receipt_number'] }}</h2>
                    </div>
                </td>
                <td style="text-align: right">
                    <img style="width: 20%" src="https://vibeadventures.be/images/logo.png" alt="">
                </td>
            </tr>



        </table>
        <h2 style="text-align: center ;background-color: #FAE8E8; padding:1%;" >This invoice was already paid.</h2>

        <table style="width: 100%;">
            <tr>
                <td style="width: 49.5%; border-style:solid;padding:3%;border-color:#F5F7F9;">
                    <div>
                        <p><b>Supplier</b></p>
                        <p>Vibe Adventures, Inc.</p>
                        <p>300 Delaware Ave,Ste 210 #549</p>
                        <p>Wilmington, 1981</p>
                        <p>US</p>
                        <br>
                        <p><b>Customer</b></p>
                        <p>{{ $data['charge_details']['billing_details']['name'] }}</p>
                        <p>{{  $data['charge_details']['billing_details']['address']['line1']  }}</p>
                        <p>{{  $data['charge_details']['billing_details']['address']['line2'].' '.$data['charge_details']['billing_details']['address']['postal_code'] }}</p>
                        <p>{{ $data['charge_details']['billing_details']['address']['state'] }}</p>
                    </div>
                </td>
                <td style="width: 1%;"></td>
                <td style="background-color:#F5F7F9; padding:3%">
                    <div style="margin-bottom:20%; text-align:center;">
                        <table>
                            <tr>
                                <td> <p><b>Booking ID</b></p> </td>
                                <td> <p>{{ $orders->booking_id }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Type</b></p> </td>
                                <td> <p>{{ $orders->flightTour->tour['type'] }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Issue Date</b></p> </td>
                                <td> <p>{{ date('Y-m-d',$data['balance_transaction']['created'] + (5 * 86400)) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Due Date</b></p> </td>
                                {{-- <td> <p>{{ date('Y-m-d',$data['payment_intent']['created'] + (5 * 86400)) }}</p></td> --}}
                                <td> <p>{{ date('Y-m-d',$data['balance_transaction']['created'] + (5 * 86400)) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Taxable Date</b></p> </td>
                                <td> <p>{{ date('Y-m-d',$data['balance_transaction']['created'] + (5 * 86400)) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Currency</b></p> </td>
                                <td> <p>{{ $data['payment_intent']['currency'] }}</p></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <table style="width: 100%;border-collapse: collapse; text-align:center">
            <tr style="background: #F5F7F9;">
                <th style="width: 10%;padding:1%"> <p>Item</p></th>
                <th style="width: 30%">Description</th>
                <th style="width: 10%">Vat</th>
                <th style="width: 15%">Unit Price</th>
                <th style="width: 10%"> Qty</th>
                <th style="width: 15%">Price inc. VAT</th>
            </tr>
            <tr>
                <td><p>Trip</p></td>
                <td><p>{{ $orders->tour_name }}</p></td>
                <td>
                    <div>
                        @if($values['adults'] > 0)
                        <p>{{ $values['tax'] }}</p>
                        @endif
                        @if($values['children'] > 0)
                        <p>{{ $values['tax'] }}</p>
                        @endif
                        @if($values['travelers'] > 0)
                        <p>{{ $values['tax'] }}</p>
                        @endif

                    </div>
                </td>
                <td>
                    <div>
                        @if($values['adults'] > 0)
                        <p>US$ {{ $values['unitPrice'] }}</p>
                        @endif
                        @if($values['children'] > 0)
                        <p>US$ {{ $values['unitPrice'] }}</p>
                        @endif
                        @if($values['travelers'] > 0)
                        <p>US$ {{ $values['unitPrice'] }}</p>
                        @endif

                    </div>
                </td>
                <td>
                    <div>
                        @if($values['adults'] > 0)
                        <p>{{ $values['adults'] }} <b style="color: #82CF45">adult(s)</b></p>
                        @endif
                        @if($values['children'] > 0)
                        <p>{{ $values['children'] }} <b style="color: #82CF45">child(s)</b></p>
                        @endif
                        @if($values['travelers'] > 0)
                        <p>{{ $values['travelers'] }} <b style="color: #82CF45">traveler(s)</b></p>
                        @endif
                    </div>
                </td>
                <td>
                    <div>
                        @if($values['adults'] > 0)
                        <p>US$ {{ $values['adults'] *  ($values['unitPrice'])  }} <b style="color: #82CF45">adult(s)</b></p>
                        @endif
                        @if($values['children'] > 0)
                        <p>US$ {{ $values['children'] *  ($values['unitPrice'])  }} <b style="color: #82CF45">child(s)</b></p>
                        @endif
                        @if($values['travelers'] > 0)
                        <p>US$ {{ $values['travelers'] *   ($values['unitPrice'])  }} <b style="color: #82CF45">infant(s)</b></p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <hr  style="border-top: 1px solid #82CF45;">
        <br>
        <div>

            <table style="text-align: center; width:100%" >
                <tr>
                    <td style="width: 70%"></td>
                    <th>Subtotal</th>
                    <td>{{ 'US $'.$orders->paid }}</td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <th>Tax</th>
                    <td><p>{{ 'US $'.$values['tax'] }}</p></td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <th>Total</th>
                    <td><b>{{ 'US $'.$orders->paid }}</b></td>
                </tr>
            </table>
        </div>
    </div>
</body>
