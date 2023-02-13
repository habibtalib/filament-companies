<?php

namespace Wallo\FilamentCompanies\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Wallo\FilamentCompanies\Contracts\AddsCompanyEmployees;
use Wallo\FilamentCompanies\FilamentCompanies;
use Livewire\Redirector;
use Wallo\FilamentCompanies\InteractsWithBanner;

class CompanyInvitationController extends Controller
{
    use InteractsWithBanner;
    /**
     * Accept a company invitation.
     *
     * @param Request $request
     * @param int $invitationId
     * @return Redirector|RedirectResponse|null
     */
    public function accept(Request $request, int $invitationId): Redirector|RedirectResponse|null
    {
        $model = FilamentCompanies::companyInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        app(AddsCompanyEmployees::class)->add(
            $invitation->company->owner,
            $invitation->company,
            $invitation->email,
            $invitation->role
        );

        $invitation->delete();

        return redirect(config('fortify.home'))->banner(
            __('Great! You have accepted the invitation to join the :company company.', ['company' => $invitation->company->name]),
        );
    }

    /**
     * Cancel the given company invitation.
     *
     * @param Request $request
     * @param int $invitationId
     * @return Redirector|RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, int $invitationId): Redirector|RedirectResponse
    {
        $model = FilamentCompanies::companyInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if (! Gate::forUser($request->user())->check('removeCompanyEmployee', $invitation->company)) {
            throw new AuthorizationException;
        }

        $invitation->delete();

        return back(303);
    }
}
