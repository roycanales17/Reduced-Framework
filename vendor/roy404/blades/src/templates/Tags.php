<?php

	namespace App\View\Compilers\Templates;

	use App\View\Compilers\Compiler;

	final class Tags extends Compiler
	{
		/**
		 * Override this method to customize how the compiler processes content.
		 *
		 * Implement a `build()` method in your subclass to manually control how
		 * wrappers and directives are applied. The following protected methods
		 * from the parent `Compiler` class are available to assist you:
		 *
		 * - `getContent()`: Retrieves the current raw or compiled content.
		 * - `getWrapper()`: Retrieves all registered wrapper configurations.
		 * - `getDirectives()`: Retrieves all registered directive callbacks.
		 * - `updateContent(string $content)`: Updates the internal content state.
		 *
		 * ### Example:
		 * ```php
		 * public function build(): void {
		 *     $content = $this->getContent();
		 *     // ... custom logic here ...
		 *     $this->updateContent($content);
		 * }
		 * ```
		 */
	}