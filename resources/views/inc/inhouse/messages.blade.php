@if (count($errors) > 0)
   
    @foreach ($errors->all() as $error)
    
        <script>
            $.toast({
                heading: 'Error Message',
                text: "{{$error}}",
                showHideTransition: 'slide',
                position : 'top-right',
                icon: 'error'
            })
        </script>
    @endforeach

@endif

@if(session('success'))
    <script>
        $.toast({
            heading: 'Success Message',
            text: "{{session('success')}}",
            showHideTransition: 'slide',
            position : 'top-right',
            icon: 'success'
        })
    </script>
@endif

@if(session('warning'))
    <script>
        $.toast({
            heading: 'Warning Message',
            text: "{{session('warning')}}",
            showHideTransition: 'slide',
            position : 'top-right',
            icon: 'warning'
        })
    </script>
@endif

@if(session('status'))
    <script>
        $.toast({
            heading: 'Success Message',
            text: "{{session('status')}}",
            showHideTransition: 'slide',
            position : 'top-right',
            icon: 'success'
        })
    </script>
@endif


@if(session('resent'))
    <script>
        $.toast({
            heading: 'Success Message',
            text: "A fresh verification link has been sent to your email address.",
            showHideTransition: 'slide',
            position : 'top-right',
            icon: 'success'
        })
    </script>
@endif
