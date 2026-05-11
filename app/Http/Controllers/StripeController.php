<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function index()
    {
        return view('payment.index');
    }

    public function checkout()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Abonnement Premium',
                    ],
                    'unit_amount' => 500,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.success', [], true),
            'cancel_url' => route('stripe.cancel', [], true),
        ]);

        return redirect($session->url);
    }

    public function success()
    {
        $user = Auth::user();
        $user->is_subscribed = true;
        $user->save();

        return redirect('/dashboard')->with('success', 'Paiement réussi ! Abonnement activé.');
    }

    public function cancel()
    {
        return redirect('/dashboard')->with('error', 'Paiement annulé.');
    }
}
