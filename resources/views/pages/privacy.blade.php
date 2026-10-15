@extends('pages.legal')
@section('title', 'Privacy Policy (draft) · Kustore')
@section('canonical', route('privacy'))
@section('noindex', true)
@section('heading', 'Privacy Policy')
@section('legal')
@php $email = config('kustore.support_email'); @endphp
<p>Kustore is run by Kuartal (PT Kuartal Financial Group). This page explains what Kustore stores, why, and what you can do about it. It covers the website at kustore.id and the Kustore apps for Android and iOS.</p>

<h2>Account data</h2>
<p>If you sign in with Kuartal ID we receive your Kuartal ID identifier, name, email and profile picture. We never receive or store your Kuartal ID password. If you sign up with email and password instead, we store your name, email and a one-way hash of your password.</p>

<h2>Your store</h2>
<p>Everything you put on your page is public once you publish it: your name, username, bio, location, website, photo, links and products. Your payment instructions, QRIS image and WhatsApp number are shown to buyers after they order.</p>

<h2>Orders</h2>
<p>When you buy from a store we keep your name, email, phone, notes and (for physical products) shipping address, and share them with that seller so they can complete your order. We email you an order confirmation, and we email the seller about the new order.</p>
<p>Payments are made directly between buyer and seller. Kustore does not receive or store card or bank account numbers.</p>

<h2>Analytics</h2>
<p>Kustore counts page views, product views and link clicks without third-party trackers or advertising tools. We do not store IP addresses; a one-way hash that changes every day is used to estimate unique visitors, and we keep the website that referred the visit.</p>

<h2>Cookies</h2>
<p>We use only the cookies needed to keep you signed in and protect forms (session and security cookies), plus a setting in your browser that remembers light or dark mode.</p>

<h2>The mobile apps</h2>
<p>The apps show the same Kustore website inside a native app. They ask for camera and photo access only when you choose to add a photo (for example a product photo or your QRIS code). The apps contain no advertising or tracking libraries.</p>

<h2>Who processes data for us</h2>
<p>Kustore is hosted by our hosting provider and sends email through our email provider. They process data only to run the service for us.</p>

<h2>Keeping and deleting data</h2>
<p>We keep your data while your account exists. You can download it at any time (Settings › Your data) and delete your account (Settings › Delete account), which permanently removes your store, products, photos, orders and customer list. Orders you placed as a buyer stay with the seller you bought from, because they need them for their records.</p>

<h2>Contact</h2>
<p>Questions or requests: <a href="mailto:{{ $email }}" class="link">{{ $email }}</a>.</p>
@endsection
