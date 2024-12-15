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
    public AmoCRMApiClient $apiClient;

    public function __construct(AmoCRMApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        $longLivedAccessToken = new LongLivedAccessToken(env('AMO_LONG_TOKEN'));
        $this->apiClient->setAccessToken($longLivedAccessToken)->setAccountBaseDomain(env('AMO_CLIENT_DOMAIN'));
    }

    public function deal()
    {
        $filter = new ContactsFilter();
        $leads = $this->apiClient->leads()->get($filter, [LeadModel::CONTACTS])->toArray();
        return inertia('Deal', compact('leads'));
    }

    public function contact($id)
    {
        return inertia('Contact');
    }

    public function store(ContactRequest $request, $id)
    {
        $data = $request->validated();

        $contact = new ContactModel();
        $contact->setName($data['name']);
        $contactModel = $this->apiClient->contacts()->addOne($contact);

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
        $this->apiClient->contacts()->updateOne($contactModel);

        $lead = $this->apiClient->leads()->getOne($id);

        $links = new LinksCollection();
        $links->add($lead);

        $this->apiClient->contacts()->link($contactModel, $links);

        $notesCollection = new NotesCollection();
        $serviceMessageNote = new ServiceMessageNote();
        $serviceMessageNote
            ->setEntityId($contactModel->getId())
            ->setService('Api Library')
            ->setText($data['comment']);

        $notesCollection = $notesCollection->add($serviceMessageNote);
        $leadNotesService = $this->apiClient->notes(EntityTypesInterface::CONTACTS);
        $leadNotesService->add($notesCollection);

        return response()->json(['status' => 1]);
    }

    public function history()
    {
        $history = $this->apiClient->events()->get()->toArray();
        return inertia('History', compact('history'));
    }
}
