@section('pageheader', 'Schemes Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Schemes Management
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Resources</li>
            <li class=""><a href="{{ route('dashboard.resources.index', ['type' => 'scheme']) }}">Schemes</a></li>
            <li class="">{{$scheme->name}}</li>
            <li class="active">{{$heading}}</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{{$heading}} <small>{{$scheme->name}}</small></h3>

                {{-- <div class="box-tools pull-right">
                    @if (Myhelper::can('add_scheme'))
                        <button class="btn btn-primary btn-sm" onclick="add()"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div> --}}
            </div>
            <form action="{{ route('dashboard.resources.submit') }}" id="chargesform" method="POST">
                <div class="box-body">
                    <table class="table table-bordered table-striped" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Charge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($providers) > 0)
                                @csrf
                                <input type="hidden" name="scheme_id" value="{{$scheme->id}}">
                                <input type="hidden" name="operation" value="scheme-chargeupdate">
                                <input type="hidden" name="product" value="{{$provider_type}}">
                                @foreach ($providers as $item)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="provider_id[]" value="{{$item->id}}">
                                            {{ $item->name }}
                                        </td>
                                        <td>
                                            <select name="type[]" class="form-control">
                                                <option {{ ($item->chargetype == 'flat') ? 'selected' : '' }} value="flat">Flat (Rs.)</option>
                                                <option {{ ($item->chargetype == 'percent') ? 'selected' : '' }} value="percent">Percent (%)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="value[]" class="form-control numberInput" value="{{ $item->chargevalue }}" placeholder="Enter Value" min="0">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No records found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">
                    @if (count($providers) > 0)
                        <button type="submit" class="btn btn-primary btn-md">Submit</button>
                    @endif
                    <button class="btn btn-danger btn-md"><a style="text-decoration: none !important; color:#fff" href="{{route('dashboard.resources.index', ['type' => 'scheme'])}}">Back</a></button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('script')
    <script>
       var numberInputs = document.getElementsByClassName('numberInput');

        for (var i = 0; i < numberInputs.length; i++) {
        numberInputs[i].addEventListener('keydown', function(event) {
            if (event.key === '-' || event.key === 'Subtract') {
            event.preventDefault();
            }
        });
        }

        $('#chargesform').validate({
            submitHandler: function() {
                var form = $('#chargesform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                            form.find('tbody').find('span.pull-right').remove();
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            setTimeout(function() {
                                window.location.href = '{{route('dashboard.resources.index', ['type' => 'scheme'])}}';
                            }, 3000); // Delay in milliseconds (2 seconds)

                           
                            $.each(data.result, function(index, values) {
                                if(values.id){
                                  //  form.find('input[value="'+index+'"]').closest('tr').find('td').eq(0).append('<span class="pull-right text-success"><i class="fas fa-check"></i></span>');
                                }else{
                                    form.find('input[value="'+index+'"]').closest('tr').find('td').eq(0).append('<span class="pull-right text-danger"><i class="fas fa-times"></i></span>');
                                    if(values){
                                        form.find('input[value="'+index+'"]').closest('tr').find('input[name="value[]"]').closest('td').append('<span class="text-danger pull-right"><i class="fas fa-times"></i> '+values+'</span>');
                                    }
                                }
                            });

                            form.find('button[type="submit"]').button('reset');
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });
    </script>
@endpush
