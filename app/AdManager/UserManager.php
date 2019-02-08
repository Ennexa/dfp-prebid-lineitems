<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\AdManager\Util\v201811\StatementBuilder;

class UserManager extends Manager
{
	protected $user;

	public function __construct($email = null, $name = null, $roleId = null)
	{
		parent::__construct();
		if ($email) {
			$this->user = $this->createUser($email, $name, $roleId);
		} else {
			$this->user = $this->getCurrentUser();
		}
	}

	public function getCurrentUser()
	{
		$userService = $this->serviceFactory->createUserService($this->session);

		$user = $userService->getCurrentUser();
		$output = [
			'userId' => $user->getId(),
			'userName' => $user->getName(),
			'userMail' => $user->getEmail(),
			'userRole' => $user->getRoleName(),
		];

		return $output;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function createUser($email, $name = null, $roleId = -1)
	{
		$userService = $this->serviceFactory->createUserService($this->session);

		$statementBuilder = (new StatementBuilder())->where('email = :email')->orderBy('id ASC')->withBindVariableValue('email', $email);
		$data = $userService->getUsersByStatement(
			$statementBuilder->toStatement()
		);
		if (null !== $data->getResults() && isset($data->getResults()[0])) {
			$user = $data->getResults()[0];
		} else {
			$user = new User();
			$user->setEmail($email);
			$user->setName($name ?: $email);
			$user->setRoleId($roleId);

			// Create the users on the server.
			$results = $userService->createUsers([$user]);
			// Print out some information for each created user.
			foreach ($results as $i => $user) {
				printf(
					"%d) User with ID %d and name '%s' was created.\n",
					$i,
					$user->getId(),
					$user->getName()
				);
			}
		}

		return [
			'userId' => $user->getId(),
			'userName' => $user->getName(),
			'userMail' => $user->getEmail(),
			'userRole' => $user->getRoleName(),
		];
	}

	public function getUserId()
	{
		$userArray = $this->getUser();

		return $userArray['userId'];
	}

	public function getAllUsers()
	{
		$userService = $this->serviceFactory->createUserService($this->session);
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $userService->getUsersByStatement(
			$statementBuilder->toStatement()
		);
		if (null !== $data->getResults()) {
			$totalResultSetSize = $data->getTotalResultSetSize();
			$i = $data->getStartIndex();
			foreach ($data->getResults() as $user) {
				printf(
					"%d) User with ID %d and name '%s' was found.\n",
					$i++,
					$user->getId(),
					$user->getName()
				);
			}
		}
	}
}
