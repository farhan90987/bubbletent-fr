<?php
namespace WPDesk\FCS\Entity;

/**
 * Represents an email template.
 */
final class EmailTemplateEntity {

	private int $id;

	private string $name;

	private string $subject;

	private string $content;

	/** @var array<int, string> */
	private array $recipients;

	private bool $enabled;

	private bool $is_default;

	/**
	 * @param int $id
	 * @param string $name
	 * @param string $subject
	 * @param string $content
	 * @param array<int, string> $recipients
	 * @param bool $enabled
	 * @param bool $is_default
	 */
	public function __construct(
		int $id,
		string $name,
		string $subject,
		string $content,
		array $recipients,
		bool $enabled,
		bool $is_default
	) {
		$this->id         = $id;
		$this->name       = $name;
		$this->subject    = $subject;
		$this->content    = $content;
		$this->recipients = $recipients;
		$this->enabled    = $enabled;
		$this->is_default = $is_default;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_subject(): string {
		return $this->subject;
	}

	/**
	 * @return array<int, string>
	 */
	public function get_recipients(): array {
		return $this->recipients;
	}

	public function get_content(): string {
		return $this->content;
	}

	public function is_enabled(): bool {
		return $this->enabled;
	}

	public function is_default(): bool {
		return $this->is_default;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_formatted_data(): array {
		return [
			'id'         => $this->id,
			'name'       => $this->name,
			'subject'    => $this->subject,
			'recipients' => implode( ', ', $this->recipients ),
			'content'    => $this->content,
			'enabled'    => $this->enabled,
			'is_default' => $this->is_default,
		];
	}
}
