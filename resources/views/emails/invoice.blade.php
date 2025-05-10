<body>
    <div style="padding: 1%">
        <table style="width: 100%">
            <tr>
                <td>
                    <div>
                        <h2 style="color: black;font-family: Canaro, sans-serif;font-size:12px">INVOICE</h2>
                        <h2 style="color: #7F91A8; font-family: Canaro, sans-serif;font-size:12px">{{ $data['charge_details']['receipt_number'] }}</h2>
                    </div>
                </td>
                <td style="text-align: right">
                    <img style="width: 30%" src="https://vibeadventures.be/images/logo.png" alt="">
                </td>
            </tr>
        </table>

        <h2 style="text-align: center; font-family: Canaro, sans-serif; color: #650808; padding:1%;font-size:12px">This invoice was already paid.</h2>

        <table style="width: 100%;margin-bottom:20px">
            <tr>
                <td style="width: 48%; padding:3%; border: 1px solid #FAFAFA;">
                    <div style="font-family: Canaro, sans-serif;font-size:10px">
                        <span><b>Supplier</b></span><br>
                        <span>Vibe Adventures, Inc.</span><br>
                        <span>300 Delaware Ave,Ste 210 #549</span><br>
                        <span>Wilmington, 1981</span><br>
                        <span>US</span>
                        <br>
                        <span><b>Customer</b></span>
                        <span>{{ $data['charge_details']['billing_details']['name'] }}</span>
                        <span>{{  $data['charge_details']['billing_details']['address']['line1']  }}</span>
                        <span>{{  $data['charge_details']['billing_details']['address']['line2'].' '.$data['charge_details']['billing_details']['address']['postal_code'] }}</span>
                        <span>{{ $data['charge_details']['billing_details']['address']['state'] }}</span>
                    </div>
                </td>
                <td style="width: 30px;"></td>
                <td style="padding:3%">
                    <div style="margin-bottom:10%; text-align:center;">
                        <table style="font-family: Canaro, sans-serif;font-size:10px">
                            <tr>
                                <td> <span><b>Booking ID</b></span> </td>
                                <td style="width: 10px;"></td>
                                <td> <span>{{ $orders->booking_id }}</span></td>
                            </tr>
                            <tr>
                                <td> <span><b>Type</b></span> </td>
                                <td style="width: 10px;"></td>
                                <td> <span>Booking</span></td>
                            </tr>
                            <tr>
                                <td> <span><b>Issue Date</b></span> </td>
                                <td style="width: 10px;"></td>
                                <td> <span>{{ date('Y-m-d',$data['balance_transaction']['created']) }}</span></td>
                            </tr>
                            <tr>
                                <td> <span><b>Due Date</b></span> </td>
                                <td style="width: 10px;"></td>
                                <td> <span>{{ date('Y-m-d',$data['balance_transaction']['created']) }}</span></td>
                            </tr>
                            <tr>
                                <td> <span><b>Taxable Date</b></span> </td>
                                <td style="width: 10px;"></td>
                                <td> <span>{{ date('Y-m-d',$data['balance_transaction']['created']) }}</span></td>
                            </tr>
                            <tr>
                                <td> <span><b>Currency</b></span> </td>
                                <td style="width: 10px;"></td>
                                <td> <span>{{ strtoupper($data['payment_intent']['currency']) }}</span></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <table style="width: 100%;border-bottom: 1px solid #82CF45; font-family: Canaro, sans-serif;font-size:10px">
            <tr style="text-align:left">
                <th style="width: 10%;padding:1%"> <span>Item</span></th>
                <th style="width: 50%">Description</th>
                <th style="width: 15%">Unit Price</th>
                <th style="width: 10%"> Qty</th>
                <th style="width: 15%">Price inc. VAT</th>
            </tr>
            <tr style="text-align:left">
                <td><span>Trip</span></td>
                <td style="border-right: 1px dotted black;">    
                    <span>
                    <span style="color: #82CF45;">{{ $orders->tour_name }} organized adventure</span>
                        ({{ \Carbon\Carbon::parse($orders->arrival)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($orders->end)->format('M d, Y') }}) for {{$orders->travelers_number}} traveler(s):
                    </span>
                    <ul>
                        <li>
                            @foreach ($orders['passengers'] as $acc)f
                                    {{ $acc['passengers'] }} 
                                    ×  <span style="color: #82CF45;">{{ $acc['name'] }}</span>
                            @endforeach
                        </li>
                        @if ($orders->attempt->duffel_res['data']['slices'])
                        <li>
                        flights (<span style="color: #82CF45;"> {{$orders->attempt->duffel_res['data']['slices'][0]['segments'][0]['origin']['iata_code']}} 
                        - {{$orders->attempt->duffel_res['data']['slices'][0]['segments'][0]['destination']['iata_code']}} </span>
                        on {{ \Carbon\Carbon::parse($orders->attempt->duffel_res['data']['slices'][0]['segments'][0]['departing_at'])->format('D, d/m') }}
                        ; <span style="color: #82CF45;">{{$orders->attempt->duffel_res['data']['slices'][1]['segments'][0]['origin']['iata_code']}} 
                        - {{$orders->attempt->duffel_res['data']['slices'][1]['segments'][0]['destination']['iata_code']}} </span>
                        on {{ \Carbon\Carbon::parse($orders->attempt->duffel_res['data']['slices'][1]['segments'][0]['departing_at'])->format('D, d/m') }}) 
                        </li>
                        @endif
                    </ul>
                </td>
                <td><span>{{ 'US $'.$orders->paid }}</span></td>
                <td><span>1</span></td>
                <td><span>{{ 'US $'.$orders->paid }}</span></td>
            </tr>
        </table>
        <br>
        <div>
            <table style="text-align: right; font-family: Canaro, sans-serif;font-size:12px width:100%" >
                <tr>
                    <td style="width: 70%"></td>
                    <th>Subtotal</th>
                    <td>{{ 'US $'.$orders->paid }}</td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <th>Tax</th>
                    <td><span>{{ 'US $'.$values['tax'] }}</span></td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <th>Total</th>
                    <td><b>{{ 'US $'.$orders->paid }}</b></td>
                </tr>
            </table>
        </div>
    </div>
    <table style="text-align:left">
        <tr>
            <td>
            <span style="font-family: Canaro, sans-serif;font-size:10px">* This is a simplified invoice. If you require an electronic invoice in CFDI format, please <a href="https://vibeadventures.com/contact" style="color: #82CF45;" >contact us</a>.</span>
            </td>
        </tr>
        <tr>
            <td>
            <span style="font-family: Canaro, sans-serif;font-size:10px">For more information visit:<a href="https://vibeadventures.com">https://vibeadventures.com</a></span>
            </td>
        </tr>
    </table>
</body>
