<?php

namespace App\View\Composers;

use App\Models\InstitutionProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class InstitutionProfileComposer
{
    public function compose(View $view): void
    {
        $view->with('institutionProfile', Cache::rememberForever(
            'institution_profile',
            fn () => InstitutionProfile::first()
        ));
    }
}
