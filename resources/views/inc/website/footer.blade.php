<!-- Footer Start -->
<footer class="copyright-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="copyright-wrapper d-flex justify-content-between align-items-center">
                    <div class="copyright-text">
                        <p>© {{date('Y')}} – <a href="{{config('app.url')}}">{{config('app.name')}}</a> | All Rights Reserved</p>
                    </div>
                    <!--copyright-text-->
                    <div class="terms-policy">
                        <ul>
                            <li><a href="{{ url('privacy-policy') }}">Privacy Policy</a></li>
                            <li><a href="{{ url('terms-conditions') }}">Terms of Service</a></li>
                        </ul>
                    </div>
                    <!--terms-policy-->
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Footer End -->
