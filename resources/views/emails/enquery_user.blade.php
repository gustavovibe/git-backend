<html>

<head>
	<title>Booking Confirmation</title>
</head>

<body>
	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" style="max-width: 203px;" valign="middle"><img alt="" border="0" class="w203px"
						src="https://vibeadventures.be/images//logo.png"
						style="display: block; max-width: 203px; width: 100%;" width="203" /></td>
				<td style="width:20px">&nbsp;</td>
				<td><span
					style="font-family: Canaro, sans-serif;font-size: 12px;color: #000000;padding-top: 5px;display: block;">If
					you need help, <span style="color: #82cf45; font-weight: bold;">contact us</span></span></td>
			</tr>
		</tbody>
	</table>

	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" bgcolor="#ffffff" style="padding: 30px;" valign="top">
					<div style="padding: 20px 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 28px; color: #000000;line-height: 34px;">
								A customer <strong style="color: #82cf45;">sent an enquiry </strong>
                        </span>
                    </div>
				</td>
			</tr>
		</tbody>
	</table>


	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Name</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{$data['name']}} {{$data['last_name'] ?? null}}
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Email</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{ $data['email'] }}
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Topic</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{ explode("|", $data['topic'])[0] }}: {{ explode("|", $data['topic'])[1] }}
                        </span>
                    </div>
				</td>
            </tr>
            @if ($data['booking_id'])
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Bookin #</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{ $data['booking_id'] }}
                        </span>
                    </div>
				</td>
            </tr>
            @endif
            @if ($data['adventure_link'] || $data['tour_details'])
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Adventure @if ($data['adventure_link']) Link @endif</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
							@if ($data['adventure_link'])
                                <a href="{{ $data['adventure_link'] }}" target="_blank">
                                    Adventure
                                </a>
                            @else
                                <a href="{{ $data['tour_details']['current_url'] }}" target="_blank" style="color: #82cf45;">
                                    {{ $data['tour_details']['tour_name'] }}
                                </a> &nbsp;
                                ({{ $data['tour_details']['tour_length_days'] }} days)
                            @endif
                        </span>
                    </div>
				</td>
            </tr>
            @endif
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px; margin" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Message</strong>
                        </span>
                    </div>
				</td>
            </tr>
            </tbody>
        </table>

    <table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%; padding-top:10px; padding-bottom:10px;">
        <tbody>
            <tr>
                <td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 5px; padding-top: 5px; border:1px solid #000000; border-radius: 5px;" valign="top">
                    <div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
                            {{ $data['message'] }}
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
        <tbody>
            <tr>
				<td align="center" bgcolor="#FFFFFF" style="padding: 0px; border:1px solid #82cf45; border-radius: 15px;" valign="top">
					<div style="padding: 10px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 10px; color: #a4a4a4; line-height: 34px;">
                            To respond to this message, just  <strong style="color: #82cf45">reply to this email</strong>.
                        </span>
                    </div>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="footer" style="margin-top:25px;">
		<table style="max-width: 600px;margin:0 auto;width: 100%;">
			<tbody>
				<tr>
					<td><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i980061126.png"
							style="vertical-align: middle;width: 150px;" /></td>
					<td><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1150146922.png"
							style="vertical-align: middle;width: 150px;" /></td>
					<td style="text-align: right;">
						<table style="width: 100%;">
							<tr>
								<td><a href="https://www.facebook.com/VibeAdventures"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/facebook.png"
											style="width:32px" /></a></td>
								<td><a href="https://www.instagram.com/vibe.adventures"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/instagram.png"
											style="width:32px" /></a></td>
								<td><a
										href="https://www.youtube.com/channel/UCQ9qyA-fVkdXarHBlzDCUBA?view_as=subscriber"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/youtube.png"
											style="width:32px" /></a></td>
							</tr>
							<tr>
								<td><a href="https://www.tiktok.com/@vibeadventures"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/tiktok.png"
											style="width:32px" /></a></td>
								<td><a href="https://www.pinterest.com/vibe_adventures/"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/pinterest.png"
											style="width:32px" /></a></td>
								<td><a href="https://twitter.com/vibe_adventures"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/twitter.png"
											style="width:32px" /></a></td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>

		<table style="max-width: 600px;margin:0 auto;width: 100%;">
			<tbody>
				<tr>
					<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Ayuntamiento
							115, Colonia Centro<br />
							<br />
							Cuauhtémoc, CDMX 06000 </span></td>
				</tr>
			</tbody>
		</table>
	</div>
</body>

</html>