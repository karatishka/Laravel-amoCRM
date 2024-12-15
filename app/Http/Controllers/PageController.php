<?php

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
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
    }

    public function deal()
    {
        $filter = new ContactsFilter();
        $leads = $this->apiClient->leads()->get($filter, [LeadModel::CONTACTS])->toArray();
        return inertia('Deal', compact('leads'));
    }

    public function contact($id)
    {
        return inertia('Contact', compact('id'));
    }

    public function store(ContactRequest $request, $id)
    {
        $lead = $this->apiClient->leads()->getOne($id);

        $data = $request->validated();

        $contact = $this->addContact($data['name'], $lead);
        $this->setPhoneField($data['phone'], $contact);
        $this->setNoteField($data['comment,'], $contact);

        return response()->json(['status' => 1]);
    }

    public function history()
    {
        $history = $this->apiClient->events()->get()->toArray();
        return inertia('History', compact('history'));
    }

    private function addContact($name, $lead)
    {
        $contact = new ContactModel();
        $links = new LinksCollection();

        $contact->setName($name);

        try {
            $contact = $this->apiClient->contacts()->addOne($contact);
        } catch (AmoCRMApiException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $this->contactUpdate($contact);

        try {
            $this->apiClient->contacts()->link($contact, $links->add($lead));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return $contact;
    }

    private function setPhoneField($phone, $contact)
    {
        $field = new CustomFieldsValuesCollection();
        $phoneField = (new MultitextCustomFieldValuesModel())->setFieldCode('PHONE');
        $field->add($phoneField);
        $phoneField->setValues(
            (new MultitextCustomFieldValueCollection())
                ->add(
                    (new MultitextCustomFieldValueModel())
                        ->setEnum('WORK')
                        ->setValue($phone)
                )
        );
        $this->contactUpdate($contact);
    }

    private function setNoteField($comment, $contact)
    {
        $notesCollection = new NotesCollection();
        $serviceMessageNote = new ServiceMessageNote();

        $serviceMessageNote
            ->setEntityId($contact->getId())
            ->setService('Api Library')
            ->setText($comment);

        $notesCollection = $notesCollection->add($serviceMessageNote);

        try {
            $leadNotesService = $this->apiClient->notes(EntityTypesInterface::CONTACTS);
            $leadNotesService->add($notesCollection);
        } catch (AmoCRMApiException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $this->contactUpdate($contact);
    }

    private function contactUpdate($contact)
    {
        try {
            $this->apiClient->contacts()->updateOne($contact);
        } catch (AmoCRMApiException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
