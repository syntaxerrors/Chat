<div class="row">
	<div class="col-md-offset-5 col-md-3">
		<div class="well">
			<div class="well-title">Create new Chat Room</div>
			{{ bForm::open() }}
				{{ bForm::text('name', null, array('id' => 'name', 'placeholder' => 'Name'), 'Name') }}
				{{ bForm::checkbox('activeFlag', 1, true, array('id' => 'activeFlag'), 'Active') }}
				{{ bForm::submitReset() }}
			{{ bForm::close(); }}
		</div>
	</div>
</div>