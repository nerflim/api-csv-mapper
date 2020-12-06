<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\CustomAttribute as Custom;

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

    private function getCustomAttributeKeys()
    {
      $custonAttributes = DB::table('custom_attributes')->select('key')->groupBy('key')->get();
      $customAttributeKeys = [];

      foreach($custonAttributes as $item) {
        array_push($customAttributeKeys, $item->key);
      }

      return $customAttributeKeys;
    }

    public function index(Request $request)
    {
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
