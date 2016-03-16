<?php
namespace App\Model\Invoice;


class Config extends \Nette\Object
{
	const
	STRUCTURE_DATE = 'structure.date',
	STRUCTURE_PRICE = 'structure.price',
	STRUCTURE_PARAMETERS = 'structure.parameters',
	STRUCTURE_ITEMS = 'structure.items',
	STRUCTURE_BANKS = 'structure.banks',
	STRUCTURE_CLIENTS = 'structure.clients',
	STRUCTURE_CONTRACTORS = 'structure.contractor',
	STRUCTURE_INVOICE = 'structure.invoice';

	const
	TABLE_INVOICES = 'invoices',
	TABLE_INVOICE_ITEMS = 'invoice_items',
	TABLE_BANK_ACCOUNTS = 'bank_accounts',
	TABLE_CLIENTS = 'clients',
	TABLE_CONTRACTORS = 'contractors';

	const
	COLUMN_ID = 'id',
	COLUMN_TOKEN = 'token',
	COLUMN_NUMBER = 'number',
	COLUMN_ISSUE_DATE = 'issue_date',
	COLUMN_DATE_DUE = 'date_due',
	COLUMN_PRICE = 'price',
	COLUMN_PRICE_VAT = 'price_vat',
	COLUMN_BANK_ACCOUNTS_ID = 'bank_accounts_id',
	COLUMN_CONTRACTORS_ID = 'contractors_id',
	COLUMN_CLIENTS_ID = 'clients_id',
	COLUMN_USERS_ID = 'users_id',
	COLUMN_PAID = 'paid',
	COLUMN_PRICING = 'pricing',
	COLUMN_SENT = 'sent',
	COLUMN_CREATED = 'created',

	COLUMN_INVOICES_ID = 'invoices_id',
	COLUMN_NAME = 'name',
	COLUMN_DESCRIPTION = 'description',
	COLUMN_PRICE_PER_UNIT = 'price_per_unit',
	COLUMN_UNITS= 'units',
	COLUMN_TOTAL = 'total',

	COLUMN_CODE = 'code',

	COLUMN_STREET = 'street',
	COLUMN_CITY = 'city',
	COLUMN_ZIP = 'zip',
	COLUMN_IC = 'ic',
	COLUMN_DIC = 'dic',

	COLUMN_PAYER_VAT = 'payer_vat',
	COLUMN_PHONE = 'phone',
	COLUMN_EMAIL = 'email';
}