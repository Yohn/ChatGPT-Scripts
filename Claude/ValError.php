<?php

class ValError {
	private $errors = [];

	public function addError(string $type, string $message, array $context = []): void {
		$this->errors[] = [
			'type' => $type,
			'message' => $this->formatMessage($message, $context),
			'context' => $context
		];
	}

	public function hasErrors(): bool {
		return !empty($this->errors);
	}

	public function getErrors(): array {
		return $this->errors;
	}

	public function getLastError(): ?array {
		return end($this->errors) ?: null;
	}

	private function formatMessage(string $message, array $context): string {
		return preg_replace_callback('/\{(\w+)\}/', function($matches) use ($context) {
			return $context[$matches[1]] ?? $matches[0];
		}, $message);
	}

	public function clear(): void {
		$this->errors = [];
	}
}
