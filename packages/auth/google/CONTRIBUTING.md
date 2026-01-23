# Contributing to Google Auth Package

Thank you for considering contributing to the Google Auth package! This document provides guidelines for contributing to the project.

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples to demonstrate the steps**
- **Describe the behavior you observed after following the steps**
- **Explain which behavior you expected to see instead and why**
- **Include screenshots or animated GIFs if possible**
- **Include your Laravel version, PHP version, and package version**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Use a clear and descriptive title**
- **Provide a step-by-step description of the suggested enhancement**
- **Provide specific examples to demonstrate the steps**
- **Describe the current behavior and explain the behavior you expected**
- **Explain why this enhancement would be useful**

### Pull Requests

- Fill in the required template
- Follow the PHP coding standards (PSR-12)
- Include tests for new features
- Update documentation as needed
- End all files with a newline
- Ensure the test suite passes
- Make sure your code lints

## Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/your-username/google-auth.git
   cd google-auth
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Create a branch:
   ```bash
   git checkout -b feature/my-new-feature
   ```

5. Make your changes and add tests

6. Run tests:
   ```bash
   composer test
   ```

7. Run code style checks:
   ```bash
   composer format
   ```

8. Commit your changes:
   ```bash
   git commit -am 'Add some feature'
   ```

9. Push to the branch:
   ```bash
   git push origin feature/my-new-feature
   ```

10. Create a Pull Request

## Coding Standards

This project follows the PSR-12 coding standard. Please ensure your code adheres to these standards.

### PHP Coding Style

- Use 4 spaces for indentation
- Use camelCase for method names
- Use PascalCase for class names
- Use snake_case for array keys and database columns
- Add type hints for all parameters and return types
- Add docblocks for all public methods

### Example:

```php
<?php

namespace Eduardoks98\GoogleAuth\Services;

class ExampleService
{
    /**
     * Process the given data.
     *
     * @param array $data The data to process
     * @return array The processed data
     */
    public function processData(array $data): array
    {
        // Implementation
        return $data;
    }
}
```

## Testing

All new features should include tests. We use PHPUnit/Pest for testing.

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

### Writing Tests

```php
<?php

namespace Eduardoks98\GoogleAuth\Tests;

use Orchestra\Testbench\TestCase;

class FeatureTest extends TestCase
{
    /** @test */
    public function it_does_something()
    {
        // Arrange
        $service = new YourService();

        // Act
        $result = $service->doSomething();

        // Assert
        $this->assertTrue($result);
    }
}
```

## Documentation

- Update the README.md if you change functionality
- Update the CHANGELOG.md following the Keep a Changelog format
- Add docblocks to all public methods
- Include examples in docblocks when helpful

## Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line
- Consider starting the commit message with an applicable emoji:
  - ✨ `:sparkles:` when adding a new feature
  - 🐛 `:bug:` when fixing a bug
  - 📝 `:memo:` when writing docs
  - 🎨 `:art:` when improving the format/structure of the code
  - ⚡ `:zap:` when improving performance
  - ✅ `:white_check_mark:` when adding tests
  - 🔒 `:lock:` when dealing with security
  - ⬆️ `:arrow_up:` when upgrading dependencies
  - ⬇️ `:arrow_down:` when downgrading dependencies

## Release Process

1. Update CHANGELOG.md with the new version
2. Update version in composer.json if needed
3. Create a git tag: `git tag v1.0.0`
4. Push the tag: `git push origin v1.0.0`
5. Create a GitHub release

## Questions?

Feel free to open an issue with your question or contact the maintainers directly.

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.
