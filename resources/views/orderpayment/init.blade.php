<form action="{{ $paytmUrl }}" method="POST" name="PAYMENT_FORM">
    @foreach ($paytmParams as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    <input type="hidden" name="CHECKSUMHASH" value="{{ $paytmChecksum }}">
</form>

<script type="text/javascript">
    document.PAYMENT_FORM.submit();
</script>
