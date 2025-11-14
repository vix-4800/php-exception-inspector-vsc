import * as assert from 'assert';
import * as vscode from 'vscode';

suite('Extension Test Suite', () => {
  vscode.window.showInformationMessage('Start all tests.');

  test('Extension should be present', () => {
    assert.ok(vscode.extensions.getExtension('vix.php-exception-inspector'));
  });

  test('Should activate extension', async function () {
    this.timeout(60000);
    const extension = vscode.extensions.getExtension('vix.php-exception-inspector');
    if (extension) {
      await extension.activate();
      assert.ok(extension.isActive);
    }
  });

  test('Should register phpExceptionInspector.analyzeFile command', async () => {
    const commands = await vscode.commands.getCommands(true);
    assert.ok(commands.includes('phpExceptionInspector.analyzeFile'));
  });

  test('Should have proper configuration', () => {
    const config = vscode.workspace.getConfiguration('phpExceptionInspector');
    assert.ok(config !== undefined);

    // Check if default values exist
    const analyzeOnSave = config.get('analyzeOnSave');
    const analyzeOnOpen = config.get('analyzeOnOpen');
    const noProjectScan = config.get('noProjectScan');

    assert.strictEqual(typeof analyzeOnSave, 'boolean');
    assert.strictEqual(typeof analyzeOnOpen, 'boolean');
    assert.strictEqual(typeof noProjectScan, 'boolean');
  });
});
