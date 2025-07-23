<?php
namespace Testing\Dto;

class CreationResult
{
	private mixed $dto;

	private mixed $entity;

	public function getDto(): mixed
	{
		return $this->dto;
	}

	public function setDto(mixed $dto): void
	{
		$this->dto = $dto;
	}

	public function getEntity(): mixed
	{
		return $this->entity;
	}

	public function setEntity(mixed $entity): void
	{
		$this->entity = $entity;
	}
}