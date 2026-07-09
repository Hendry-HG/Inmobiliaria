@extends('layouts.landing')

@section('title', 'Tu hogar en Venezuela')

@section('content')
    <!-- Hero Slider -->
    <x-hero-slider />

    <!-- Search Filters -->
    <x-search-filters :states="$states ?? []" />

    <!-- Featured Properties -->
    <x-featured-properties />

    <!-- Recent Properties -->
    <x-recent-properties
        :limit="8"
        title="Propiedades Recientes"
        subtitle="Descubre las propiedades que nuestros asesores acaban de publicar"
        badge="Recientes"
    />

    <!-- Sell Section -->
    <x-sell-section />
@endsection
