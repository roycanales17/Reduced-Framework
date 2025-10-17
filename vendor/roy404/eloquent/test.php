<?php
	
	use Illuminate\Databases\DB;
	
	require_once 'vendor/autoload.php';
	
	$user = DB::table( 'user' )
		->select( 'name' )
		->select( 'email' )
		->select( 'contact' )
		->where( function( \Illuminate\Databases\Eloquent $group ) {
			$group->where( 'name', '<>', 'robot' )
				->orWhere( 'name', '<>', 'AI' );
		})
		->where( 'email', 'canales.robroy123@gmail.com' )
		->field();