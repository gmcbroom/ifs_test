Welcome {{ $mydata['first_name'] }}
Thank you for your application.
Please read the following terms and conditions for the use of our site. Clicking on the link below signifies your acceptance of these terms. After which your application will be forwarded to our staff for processing.

Once processed, your account will be enabled and one of our employees will be in touch to assist you in your first use of the system.

Please note that without acceptance, your application will not proceed any further.

Acceptance of Terms

Please paste the following URL into your browser to accept our terms and conditions :-

{{ url('user/'.$mydata['verify_token'],'verify') }}

Credentials
    Username : {{ $mydata['email'] }}
    Password : {{ $mydata['password'] }}

Please note that your password is not stored on our system so should you lose it we will be unable to retrieve it. Instead please contact us to have your password reset.
Yours

Admin
