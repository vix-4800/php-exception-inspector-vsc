# Vigil - Throws Analyzer

PHP utility to analyze `@throws` documentation tags and detect undeclared exceptions.

Designed for IDE integration with JSON output.

## Features

- Parses PHP files using nikic/php-parser
- Analyzes `@throws` tags in docblocks
- Detects thrown exceptions not declared in docblocks
- **Detects documented exceptions that are never thrown**
- **Cross-file analysis: tracks exceptions from called methods**
- **Automatic project structure detection via composer.json autoload**
- **Static method call analysis (including use-imports resolution)**
- JSON output for easy integration with editors/IDEs

## Installation

```bash
composer install
```

## Usage

```bash
# Analyze a file
./bin/vigil tests/MyClass.php

# Analyze a directory
./bin/vigil src/

# Show help
./bin/vigil --help
```

## Output Format

All output is in JSON format:

```json
{
  "files": [
    {
      "file": "path/to/file.php",
      "errors": [
        {
          "line": 15,
          "type": "undeclared_throw",
          "exception": "RuntimeException",
          "function": "methodName",
          "message": "Exception 'RuntimeException' is thrown but not declared in @throws tag"
        },
        {
          "line": 20,
          "type": "unnecessary_throws",
          "exception": "InvalidArgumentException",
          "function": "anotherMethod",
          "message": "Exception 'InvalidArgumentException' is documented in @throws but never thrown"
        },
        {
          "line": 25,
          "type": "undeclared_throw_from_call",
          "exception": "LogicException",
          "function": "caller",
          "called_method": "callee",
          "called_class": "Namespace\\ClassName",
          "message": "Exception 'LogicException' can be thrown by 'callee()' but is not declared in @throws tag"
        }
      ]
    }
  ],
  "summary": {
    "total_files": 1,
    "files_with_errors": 1,
    "total_errors": 3
  }
}
```

### Error Types

- **`undeclared_throw`** - Exception is thrown but not declared in `@throws` tag
- **`unnecessary_throws`** - Exception is documented in `@throws` but never actually thrown
- **`undeclared_throw_from_call`** - Exception can be thrown by a called method but not declared in current method's `@throws`
- **`parse_error`** - PHP syntax error in the file

### Error Output

If an error occurs during analysis:

```json
{
  "error": {
    "message": "Error message",
    "file": "path/to/file.php",
    "line": 123
  }
}
```

## Exit Codes

- `0` - Success, no errors found
- `1` - Errors found in analyzed files
- `2` - Invalid usage or runtime error

## Development

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage

# Run specific test
vendor/bin/phpunit tests/Unit/AnalyzerTest.php
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
