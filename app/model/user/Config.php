<?php
namespace App\Model\User;


class Config extends \Nette\Object
{
	const
	DEFAULT_ROLE = 3;

	/** USER DATABASE TABLE CONSTANTS */
	const
	TABLE_USERS = 'users',
	TABLE_USER_ROLES = 'user_roles',
	TABLE_USER_HAS_ROLE = 'user_has_role';

	/** USER DATA STRUCTURE CONSTANTS */
	const
	STRUCTURE_NAME = 'structure.name',
	STRUCTURE_CONTACT = 'structure.contact',
	STRUCTURE_PASSWORD = 'structure.password',
	STRUCTURE_REGISTRATION = 'structure.registration';

	/** USER DATABASE COLUMN CONSTANTS */
	const
	COLUMN_ID = 'id',
	COLUMN_ACTIVE = 'active',
	COLUMN_CREATED = 'created',
	COLUMN_NAME = 'name',
	COLUMN_SURNAME = 'surname',
	COLUMN_EMAIL = 'email',
	COLUMN_TOKEN = 'token',
	COLUMN_PASSWORD = 'password',
	COLUMN_PASSWORD_TOKEN = 'password_token',
	COLUMN_PASSWORD_EXPIRATION = 'password_expiration',
	COLUMN_LANGUAGES_ID = 'languages_id',

	COLUMN_USERS_ID = 'users_id',
	COLUMN_ROLES_ID = 'user_roles_id',

	COLUMN_KEY_NAME = 'key_name',

	COLUMN_ROLES = 'roles';
}