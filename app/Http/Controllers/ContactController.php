<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the all the distinct custom attribute keys that will be displayed in the table header to frontend
     *
     * @return Array<Strings>
     */
    private function getCustomAttributeKeys()
    {
      $custonAttributes = DB::table('custom_attributes')->select('key')->groupBy('key')->get();
      $customAttributeKeys = [];

      foreach($custonAttributes as $item) {
        array_push($customAttributeKeys, $item->key);
      }

      return $customAttributeKeys;
    }

    /**
     * Get the the contacts with merged custom attributes
     * It will get the contacts first with pagination
     * Then it will modify the collection data to merge the custom attributes
     *
     * @return JSON
     */
    public function index(Request $request)
    {
      $this->validate($request, [
        'page' => 'required|integer',
        'pageSize' => 'required|integer',
        'sortBy' => 'required_with:sort'
      ]);

      $sort   = $request->sort;
      $sortBy = $request->sortBy;

      $contacts = DB::table('contacts')
        ->select('id', 'team_id', 'name', 'phone', 'email', 'sticky_phone_number_id')
        ->when($sort, function ($query, $sort) use ($sortBy) {
          return $query->orderBy($sort, $sortBy);
        })
        ->paginate($request->pageSize)
        ->toArray();
      
      $data = [];
      
      foreach ($contacts['data'] as $item) {
        $customAttributes = DB::table('custom_attributes')
          ->where('contact_id', $item->id)
          ->get();

        foreach($customAttributes as $customItem) {
          $item->{$customItem->key} = $customItem->value;
        }

        array_push($data, $item);
      };

      $contacts['data'] = $data;

      // get the custom headers
      $customAttributeKeys = $this->getCustomAttributeKeys();

      return $this->response(['contacts' => $contacts, 'customAttributes' => $customAttributeKeys]);
    }

    /**
     * Adds bulk contacts with custom attributes
     * Loop through each contact data and store the data in contacts
     * If there is a custom attribute found, store to custom_attributes
     *
     * @return JSON
     */
    public function import(Request $request)
    {
      $contactKeys = ['team_id', 'name', 'phone', 'email', 'sticky_phone_number_id'];

      $this->validate($request, [
          'data' => 'required|array|min:1',
          'keys' => 'required|array|min:' . count($contactKeys)
      ]);
      
      foreach ($request->data as $data) {
        // store contact
        $contact = DB::table('contacts')->insertGetId([
          'team_id'                 => $data['team_id'],
          'name'                    => $data['name'],
          'phone'                   => $data['phone'],
          'email'                   => $data['email'],
          'sticky_phone_number_id'  => $data['sticky_phone_number_id'],
        ]);

        // get custom header
        foreach($data as $key => $value) {
          if (!in_array(strtolower($key), $contactKeys)) {
            $custom = DB::table('custom_attributes')->insert([
              'contact_id'  => $contact,
              'key'         => $key,
              'value'       => $value
            ]);
          }
        }
      }

      return $this->response(['success' => true, 'description' => 'Contacts saved successfully!']);
    }
}
