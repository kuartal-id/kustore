@extends('pages.legal')
@section('title', 'Privacy Policy (draft) · Kustore')
@section('canonical', route('privacy'))
@section('noindex', true)
@section('heading', 'Privacy Policy')
@section('legal')
<p>This draft explains what Kustore stores and why.</p>
<h2>Account data</h2>
<p>If you sign in with Kuartal ID we receive your Kuartal ID identifier, name, email and profile picture. We never receive or store your Kuartal ID password.</p>
<h2>Orders</h2>
<p>When you buy from a store we share your name, email, phone and (for physical products) shipping address with that seller so they can complete your order.</p>
<h2>Analytics</h2>
<p>Kustore counts page views and link clicks without third-party trackers. We do not store IP addresses; a one-way hash that changes every day is used to estimate unique visits.</p>
<h2>Your rights</h2>
<p>You can ask us to export or delete your data at hello@kustore.id (placeholder).</p>
@endsection
