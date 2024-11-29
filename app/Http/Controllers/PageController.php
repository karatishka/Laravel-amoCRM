<?php

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\NoteType\ServiceMessageNote;
use App\Models\ContactRequest;


class PageController extends Controller
{
    public function deal()
    {
        $apiClient = new AmoCRMApiClient();
        $longLivedAccessToken = new LongLivedAccessToken(env('AMO_LONG_TOKEN'));
        $apiClient->setAccessToken($longLivedAccessToken)->setAccountBaseDomain(env('AMO_CLIENT_DOMAIN'));

        $filter = new ContactsFilter();
        $leads = $apiClient->leads()->get($filter, [LeadModel::CONTACTS])->toArray();
        return inertia('Deal', compact('leads'));
    }

    public function contact($id)
    {
        return inertia('Contact');
    }

    public function store(ContactRequest $request, $id)
    {
        $data = $request->validated();

        $apiClient = new AmoCRMApiClient();
        $longLivedAccessToken = new LongLivedAccessToken(env('AMO_LONG_TOKEN'));
        $apiClient->setAccessToken($longLivedAccessToken)->setAccountBaseDomain(env('AMO_CLIENT_DOMAIN'));

        $contact = new ContactModel();
        $contact->setName($data['name']);
        $contactModel = $apiClient->contacts()->addOne($contact);

        $customFields = new CustomFieldsValuesCollection();
        $phoneField = (new MultitextCustomFieldValuesModel())->setFieldCode('PHONE');
        $customFields->add($phoneField);
        $phoneField->setValues(
            (new MultitextCustomFieldValueCollection())
                ->add(
                    (new MultitextCustomFieldValueModel())
                        ->setEnum('WORK')
                        ->setValue($data['phone'])
                )
        );
        $contact->setCustomFieldsValues($customFields);
        $apiClient->contacts()->updateOne($contactModel);

        $lead = $apiClient->leads()->getOne($id);

        $links = new LinksCollection();
        $links->add($lead);

        $apiClient->contacts()->link($contactModel, $links);

        $notesCollection = new NotesCollection();
        $serviceMessageNote = new ServiceMessageNote();
        $serviceMessageNote
            ->setEntityId($contactModel->getId())
            ->setService('Api Library')
            ->setText($data['comment']);

        $notesCollection = $notesCollection->add($serviceMessageNote);
        $leadNotesService = $apiClient->notes(EntityTypesInterface::CONTACTS);
        $leadNotesService->add($notesCollection);

        return response()->json(['status' => 1]);
    }

    public function history()
    {
        $apiClient = new AmoCRMApiClient();
        $longLivedAccessToken = new LongLivedAccessToken(env('AMO_LONG_TOKEN'));
        $apiClient->setAccessToken($longLivedAccessToken)->setAccountBaseDomain(env('AMO_CLIENT_DOMAIN'));

        $history = $apiClient->events()->get()->toArray();
        return inertia('History', compact('history'));
    }
}
