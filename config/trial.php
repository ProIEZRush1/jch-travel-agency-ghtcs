<?php

return [
    // When true, the system is in a 5-day TRIAL: it's fully visible/usable EXCEPT the actions the
    // business uses to sell / receive money (checkout, orders, bookings, payments, publishing, sending
    // to real customers…), which are gated behind a "activa con tu anticipo" lock. Flipping this to
    // false (env TRIAL_LOCKED=false) on payment unlocks everything with no rebuild.
    'locked' => filter_var(env('TRIAL_LOCKED', false), FILTER_VALIDATE_BOOLEAN),
];
