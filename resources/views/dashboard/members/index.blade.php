@section('pageheader', $role->name)
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Member Management
            <small>Manage {{$role->name}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Member Management</li>
            <li class="active">{{$role->name}}</li>
        </ol>
    </section>
    

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All {{$role->name}}s</h3>

                <div class="box-tools pull-right">
                    @if(Myhelper::can('add_'.$role->slug))
                        <a href="{{route('dashboard.members.add', ['type' => $type])}}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add New</a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            @if(in_array($type, ['user']))
                                <th>Referral Code</th>
                                <th>Wallet</th>
                            @endif
                            @if(in_array($type, ['branch']))
                                <th>Category</th>
                                <th>Wallet</th>
                            @endif
                            @if(in_array($type, ['deliveryboy']))
                                <th>Earning Wallet</th>
                                <th>Collection Wallet</th>
                            @endif
                            <th>Created On</th>
                            <th>Status</th>
                            <th>Verification Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $('#my-datatable').DataTable({
            // stateSave: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => $type])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                },
            },
            columns:[
                {
                    data:'id',
                    name: 'id',
                    render: function(data, type, full, meta){
                        return '<b class="text-primary">' + data + '</b>';
                    },
                },
                {
                    data:'name',
                    name: 'name',
                    render: function(data, type, full, meta){
                        if (full.role.slug == 'deliveryboy'){
                            return data.slice(0, 10) + '...';
                        }else{
                            return data;
                        }
                    },
                    searchable: true,
                },
                {
                    data:'email',
                    name: 'email',

                   
                    
                    render: function(data, type, full, meta){
                        if (full.role.slug == 'deliveryboy'){
                            return data.slice(0, 10) + '...';
                        }else{
                            return data;
                        }
                       
                    },
                },
                {
                    data:'mobile',
                    name: 'mobile',
                    render: function(data, type, full, meta){
                        if(data != null){
                            return data;
                        } else{
                            return 'N/A';
                        }
                    },
                },
                /* @if(in_array($type, ['user'])) */
                {
                    data:'referral_code',
                    name: 'referral_code',
                    render: function(data, type, full, meta){
                        return `<b class="text-uppercase">` + (data ?? `-`) + `</b>`
                    },
                },
                {
                    data:'userwallet',
                    name: 'userwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                /* @endif */
                /* @if(in_array($type, ['branch'])) */
                {
                    data:'business_category',
                    name: 'business_category',
                    render: function(data, type, full, meta){
                        return `<b class="text-uppercase">` + (data ?? `N/A`) + `</b>`
                    },
                },
                {
                    data:'branchwallet',
                    name: 'branchwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                /* @endif */
                /* @if(in_array($type, ['deliveryboy'])) */
                {
                    data:'riderwallet',
                    name: 'riderwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                {
                    data:'creditwallet',
                    name: 'creditwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                /* @endif */
                {
                    data:'created_at',
                    name: 'created_at',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
               
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        if(data == 1){
                            html = `<a onclick="changeAction(`+full.id+`)" class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i>&nbsp;Active</a>`;
                        } else{
                            html = `<a onclick="changeAction(`+full.id+`)" class="btn btn-sm btn-warning"><i class="fa fa-remove"></i>&nbsp;Inactive</a>`;
                        }

                        return html;
                    },
                    className: 'text-center'
                },
                {
                    data:'mobile_verified_at',
                    name: 'mobile_verified_at',
                    render: function(data, type, full, meta){
                        if(data !=null){
                            html = `<button class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i>&nbsp;Done</button>`;
                        } else{
                            html = `<button class="btn btn-sm btn-warning"><i class="fa fa-remove"></i>&nbsp;Pending</button>`;
                        }

                        return html;
                    },
                    className: 'text-center'
                },
                {
                render: function(data, type, full, meta){
                    console.log(full);
                    var html = '';
                    fullId = full.id;
                    if (full.mobile_verified_at !=null && full.status == 1){
                            /* @if(Myhelper::can('edit_'.$role->slug)) */
                            html += `<a class="btn btn-xs btn-primary mg" href="{{ route('dashboard.profile') }}/${btoa(fullId)}"><i class="fa fa-pencil"></i></a>`;
                        
                        /* @if(in_array($role->slug, ['admin','branch', 'deliveryboy'])) */
                            html += `<a class="btn btn-xs btn-warning mg" href="{{ route('dashboard.members.permission') }}/${btoa(fullId)}"><i class="fa fa-lock"></i></a>`;
                            
                            html += `<a title="login" class="btn btn-xs btn-success mg" href="/admin-merchant-login/${fullId}"><i class="fa fa-sign-in"></i></a>`;                    
                        /* @endif */
                    /* @endif */
                    }else{
                           /* @if(Myhelper::can('edit_'.$role->slug)) */
                           html += `<a class="btn btn-xs btn-primary mg" href="{{ route('dashboard.profile') }}/${btoa(fullId)}"><i class="fa fa-pencil"></i></a>`;
                        
                        /* @if(in_array($role->slug, ['admin','branch', 'deliveryboy'])) */
                            html += `<a class="btn btn-xs btn-warning mg" href="{{ route('dashboard.members.permission') }}/${btoa(fullId)}"><i class="fa fa-lock"></i></a>`;
                            html += `<a title="login" class="btn btn-xs btn-success disabled mg" href="/admin-merchant-login/${fullId}"><i class="fa fa-sign-in"></i></a>`;
                                              
                        /* @endif */
                    /* @endif */
                    }

                  

                    return html;
                }
            }
            ],
            "order": [
                [0, 'asc']
            ]
        });

        function changeAction(id){
            @if(!Myhelper::can('edit_'.$type))
                return false;
            @endif

            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.members.changeaction')}}",
                    method: "POST",
                    data: {'_token':'{{csrf_token()}}','role':'{{$role->slug}}','id':id},
                    success: function(data){
                        notify(data.status, 'success');
                        $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }
    </script>
@endpush
