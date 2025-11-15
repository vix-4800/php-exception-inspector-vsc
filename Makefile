.PHONY: help build-phar build-vsix build-all clean test install dev package

# Build PHP PHAR archive
build-phar:
	@echo "Building PHP PHAR archive..."
	@box compile
	@echo "✓ PHAR build complete"

# Build VSCode extension package
build-vsix:
	@echo "Building VSCode extension..."
	@npm run compile
	@npx vsce package
	@echo "✓ VSIX build complete"

# Build everything
build-all: build-phar build-vsix
	@echo "✓ All builds complete"

# Clean build artifacts
clean:
	@echo "Cleaning build artifacts..."
	@rm -rf out/
	@rm -f php-exception-inspector.phar
	@rm -f *.vsix
	@echo "✓ Clean complete"

# Run all tests
test: test-php test-ts
	@echo "✓ All tests complete"

# Run PHP tests
test-php:
	@echo "Running PHP tests..."
	@composer test

# Run TypeScript tests
test-ts:
	@echo "Running TypeScript tests..."
	@npm test

# Install dependencies
install:
	@echo "Installing dependencies..."
	@npm install
	@composer install
	@echo "✓ Dependencies installed"
