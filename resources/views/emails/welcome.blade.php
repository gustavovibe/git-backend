<html>

<head>
	<title>Booking Confirmation</title>
</head>

<body>
	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" style="max-width: 203px;" valign="middle">
                    <a href="{{ rtrim(config('frontend.url'), '/') }}/" target="_blank">
                        <img alt="" border="0" class="w203px"
						src="https://vibeadventures.be/images//logo.png"
						style="display: block; max-width: 203px; width: 100%;" width="203" />
                    </a>
                </td>
				<td style="width:20px">&nbsp;</td>
				<td><span
					style="font-family: Canaro, sans-serif;font-size: 12px;color: #000000;padding-top: 5px;display: block;">If
					you need help, <a style="color: #82cf45; font-weight: bold;" href="{{ rtrim(config('frontend.url'), '/') }}/contact" target="_blank">contact us</a></span></td>
			</tr>
		</tbody>
	</table>

	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" bgcolor="#ffffff" style="padding: 0px;" valign="top">
					
    <div style="background-color:white; padding:5%">
        <br>
        <div>
            <h1>Hi <span style="color: #82CF45">{{ $data['name'] }}</span>, your account was created!</h1>
        </div>
        <br>
        <div>
            <div style="background-color: rgba(130, 207, 69, 0.25); height: 40px;display: flex; align-items: center; justify-content: center;">
                <h3><b>This is an automated email, please do not reply to it.</b></h3>
            </div>
            <br>
            <div style="color: gray;">
                <p>To access your booking, we’ve created an account for you. Here are the login details:</p>
                <p style="padding-left: 40px;">Your username: <b>{{ $data['email'] }}</b></p>
                <p style="padding-left: 40px;">Your automatic generated password: <b>{{' '.$data['password'] }}</b></p>
                <p>Now you can log in to your account to view your orders and change your password.</p>

                <div style="display: flex; align-items: center; justify-content: center;">
                    <a href="{{ rtrim(config('frontend.url'), '/') }}/" target="_blank" style="background-color: #FF6C0E; padding:1%; color:white; border-radius:5px;font-weight:bold;text-decoration: none;">See account</a>
                </div>
                <p>A warm greeting,</p>
                <p><b>Vibe Adventures team</b></p>
            </div>
        </div>
        <br>
    <div>
				</td>
			</tr>
		</tbody>
	</table>


	<div class="footer" style="margin-top:25px;">
		<table style="max-width: 600px;margin:0 auto;width: 100%;">
			<tbody>
				<tr>
					<td><a href="https://www.trustindex.io/reviews/vibeadventures.com" target="_blank"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i980061126.png"
							style="vertical-align: middle;width: 150px;" /></a></td>
					<td><a href="https://www.trustindex.io/reviews/vibeadventures.com" target="_blank"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1150146922.png"
							style="vertical-align: middle;width: 150px;" /></a></td>
					<td style="text-align: right;">
						<table style="width: 100%;">
							<tr>
								<td><a href="https://www.facebook.com/VibeAdventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/facebook.png"
											style="width:32px" /></a></td>
								<td><a href="https://www.instagram.com/vibe.adventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/instagram.png"
											style="width:32px" /></a></td>
								<td><a
										href="https://www.youtube.com/channel/UCQ9qyA-fVkdXarHBlzDCUBA?view_as=subscriber" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/youtube.png"
											style="width:32px" /></a></td>
							</tr>
							<tr>
								<td><a href="https://www.tiktok.com/@vibeadventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/tiktok.png"
											style="width:32px" /></a></td>
								<td><a href="https://www.pinterest.com/vibe_adventures/" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/pinterest.png"
											style="width:32px" /></a></td>
								<td><a href="https://twitter.com/vibe_adventures" target="_blank"><img
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