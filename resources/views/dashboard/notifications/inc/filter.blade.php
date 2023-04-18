<div class="box">
    <form id="searchform">
        <div class="box-body">
            <div class="row">
                {{-- <div class="form-group col-md-3">
                    <label>User Role</label>
                    <select name="role_id" class="form-control select2">
                        <option value="">Select Role</option>
                        @foreach ($roles as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="form-group col-md-3">
                    <label>User Status</label>
                    <select name="status" class="form-control select2">
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="box-footer text-right">
            <button type="button" onclick="resetsearch()" class="btn btn-md btn-danger"><i class="fa fa-remove"></i>&nbsp;Reset</button>
            <button type="submit" class="btn btn-md btn-primary"><i class="fa fa-search"></i>&nbsp;Filter</button>
        </div>
    </form>
</div>

@push('script')
    <script>
        function resetsearch(){
            $('#searchform')[0].reset();
            $('#searchform').find('select').val('').trigger('change')
            $('#my-datatable').dataTable().api().ajax.reload();
            document.getElementById('select-all-checkbox').checked = false;
        }
    </script>
@endpush
