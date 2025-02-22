<body>
    <div style="padding: 3%">

        <table style="width: 100%">
            <tr>
                <td>
                    <div>
                        <h2>INVOICE</h2>
                        <h2>{{ $data['charge_details']['receipt_number'] }}</h2>
                    </div>
                </td>
                <td style="text-align: right">
                    <img style="width: 20%" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png')))}}" alt="">
                </td>
            </tr>



        </table>
        <h2 style="text-align: center">This invoice was already paid.</h2>

        <table style="width: 100%;">
            <tr>
                <td style="width: 49.5%; border-style:solid;padding:2%;border-color:gray;">
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
                <td style="background-color:gray; padding:2%">
                    <div >
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
                                <td> <p>{{ date('Y-m-d',$data['payment_intent']['created'] + (5 * 86400)) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Taxable Date</b></p> </td>
                                <td> <p>554-5546-4654</p></td>
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
        {{-- <table style="width: 100%;">
            <tr>
                <th> <p>Item</p></th>
                <th>Description</th>
                <th>Vat</th>
                <th>Unit Price</th>
                <th> Qty</th>
                <th>Price inc. VAT</th>
            </tr>
            <tr>
                <td><p>Trip</p></td>
                <td><p>{{ $orders->tour->description }}</p></td>
                <td><p>0%</p></td>
                <td><p>Unit Price</p></td>
                <td><p>2 adults</p></td>
                <td><p>price inc.vat</p></td>
            </tr>
        </table> --}}

        <br>
        <div style=" width:100%">

            <table style="text-align: center;" >
                <tr>
                    <th>Subtotal</th>
                    <td>Us$1,989.09</td>
                </tr>
                <tr>
                    <th>Tax</th>
                    <td>Us$1,989.09</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td>Us$1,989.09</td>
                </tr>
            </table>
        </div>
    </div>
</body>
