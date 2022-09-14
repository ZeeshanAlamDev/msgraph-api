<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HubspotController extends Controller
{
    public function create_contact($body)
    {
        $contact = [];
        $response = '';
        $access_token = config('msgraph.hubspot_token');
        $hubspot = \HubSpot\Factory::createWithAccessToken($access_token);

        // Check, if already exists, then try to update
        $filter = new \HubSpot\Client\Crm\Contacts\Model\Filter();
        $filter
            ->setOperator('EQ')
            ->setPropertyName('email')
            ->setValue($body['email']);
        $filter_group = new \HubSpot\Client\Crm\Contacts\Model\FilterGroup();
        $filter_group->setFilters([$filter]);
        $search_request = new \HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest();
        $search_request->setFilterGroups([$filter_group]);
        $contacts_data = $hubspot->crm()->contacts()->searchApi()->doSearch($search_request);

        if (!empty($contacts_data['results'][0]['properties']['hs_object_id'])) {
            $contact_id = $contacts_data['results'][0]['properties']['hs_object_id'];
            $new_properties = new \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput();
            $new_properties->setProperties($body);

            $contact = $hubspot->crm()->contacts()->basicApi()->update($contact_id, $new_properties);
            $contact = !empty($contact['properties']) ? (object) $contact['properties'] : [];

            return $contact;
        }

        // else create new contact
        try {
            $request = new \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput();
            $request->setProperties($body);
            $response = $hubspot->crm()->contacts()->basicApi()->create($request);
            $contact = json_decode($response);
            $contact = !empty($contact->properties) ? $contact->properties : [];
        } catch (\Exception$e) {
            $contact = [];
        }

        return $contact;
    }
}
