<?php

namespace Modules\Tattoo\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Tattoo\Models\TattooAppointment;
use Modules\Tattoo\Models\TattooArtist;
use Modules\Tattoo\Models\TattooConsent;
use Modules\Tattoo\Models\TattooSession;

class TattooDashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $todayAppointments = TattooAppointment::whereDate('appointment_date', $today)
            ->count();

        $yesterdayAppointments = TattooAppointment::whereDate('appointment_date', now()->subDay()->toDateString())
            ->count();

        $appointmentsDiff = $todayAppointments - $yesterdayAppointments;

        $pendingSessions = TattooSession::whereIn('status', ['pending', 'in_progress'])
            ->count();

        $sessionsNeedConfirmation = TattooSession::where('status', 'pending')->count();

        $pendingConsents = TattooConsent::where(function ($q) {
            $q->whereNull('signed_date')
              ->orWhere('accepted', false);
        })->count();

        $monthlyIncome = TattooAppointment::whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->sum('estimated_price');

        $previousMonthIncome = TattooAppointment::whereMonth('appointment_date', now()->copy()->subMonth()->month)
            ->whereYear('appointment_date', now()->copy()->subMonth()->year)
            ->sum('estimated_price');

        $incomeVariationText = 'Sin cambios';
        if ((float) $previousMonthIncome > 0) {
            $variation = (($monthlyIncome - $previousMonthIncome) / $previousMonthIncome) * 100;
            $incomeVariationText = ($variation >= 0 ? '+' : '') . number_format($variation, 1) . '% vs mes anterior';
        } elseif ((float) $monthlyIncome > 0) {
            $incomeVariationText = '+100% vs mes anterior';
        }

        $upcomingAppointments = TattooAppointment::with(['person', 'artist', 'style'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        $topArtists = TattooArtist::query()
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->take(5)
            ->get();

        return view('tattoo::dashboard.index', compact(
            'todayAppointments',
            'yesterdayAppointments',
            'appointmentsDiff',
            'pendingSessions',
            'sessionsNeedConfirmation',
            'pendingConsents',
            'monthlyIncome',
            'previousMonthIncome',
            'incomeVariationText',
            'upcomingAppointments',
            'topArtists'
        ));
    }
}