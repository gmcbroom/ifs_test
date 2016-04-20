<html>
    <body>
        <h1>Welcome {{ $mydata['first_name'] }}</h1>
        <p>Thank you for your application.</p>
        <p>Please read the following terms and conditions for the use of our site. Clicking on the link below signifies your acceptance of these terms. After which your application will be forwarded to our staff for processing.</p>

        <p>Once processed, your account will be enabled and one of our employees will be in touch to assist you in your first use of the system.</p?

        <p> Please note that without acceptance, your application will not proceed any further.</p>
        <h1>Acceptance of Terms</h1>
        <a href="{{ url('user/'.$mydata['verify_token'],'verify') }}">Click to accept</a>
        <h1>Credentials</h1>
        <p>
            Username : {{ $mydata['email'] }}<br>
            Password : {{ $mydata['password'] }}<br>
        </p>
        Please note that your password is not stored on our system so should you lose it we will be unable to retrieve it. Instead please contact us to have your password reset.</p>
    Yours<br>
    <br>
    Admin
</p>
</body>
</html>