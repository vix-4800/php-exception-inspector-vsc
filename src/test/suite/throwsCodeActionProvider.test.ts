import * as assert from 'assert';
import * as vscode from 'vscode';
import { ThrowsCodeActionProvider } from '../../ThrowsCodeActionProvider';

suite('ThrowsCodeActionProvider Test Suite', () => {
  vscode.window.showInformationMessage('Start ThrowsCodeActionProvider tests.');

  test('Should provide quick fix for missing @throws', async () => {
    const provider = new ThrowsCodeActionProvider();

    // Create a test document with PHP code
    const content = `<?php

class TestClass {
    public function testMethod() {
        throw new \\Exception('test');
    }
}
`;

    const document = await vscode.workspace.openTextDocument({
      content: content,
      language: 'php',
    });

    // Create a diagnostic simulating our extension's output
    const diagnostic = new vscode.Diagnostic(
      new vscode.Range(4, 0, 4, 100),
      'Missing @throws tag for Exception: Exception',
      vscode.DiagnosticSeverity.Warning
    );
    diagnostic.source = 'PHP Exception Inspector';
    diagnostic.code = 'missing_throws';
    (diagnostic as any).exceptionName = 'Exception';

    const context: vscode.CodeActionContext = {
      diagnostics: [diagnostic],
      only: undefined,
      triggerKind: vscode.CodeActionTriggerKind.Automatic,
    };

    const actions = provider.provideCodeActions(
      document,
      new vscode.Range(4, 0, 4, 100),
      context,
      {} as vscode.CancellationToken
    );

    assert.ok(actions, 'Should return code actions');
    assert.strictEqual(actions.length, 1, 'Should return one action');
    assert.strictEqual(
      actions[0].title,
      'Add @throws Exception',
      'Action should have correct title'
    );
    assert.ok(actions[0].edit, 'Action should have edit');
  });

  test('Should insert @throws into existing docblock', async () => {
    const provider = new ThrowsCodeActionProvider();

    // Create a test document with PHP code that has existing docblock
    const content = `<?php

class TestClass {
    /**
     * Test method description
     * @param string $test
     * @return void
     */
    public function testMethod(string $test) {
        throw new \\InvalidArgumentException('test');
    }
}
`;

    const document = await vscode.workspace.openTextDocument({
      content: content,
      language: 'php',
    });

    // Create a diagnostic
    const diagnostic = new vscode.Diagnostic(
      new vscode.Range(9, 0, 9, 100),
      'Missing @throws tag for InvalidArgumentException: InvalidArgumentException',
      vscode.DiagnosticSeverity.Warning
    );
    diagnostic.source = 'PHP Exception Inspector';
    diagnostic.code = 'missing_throws';
    (diagnostic as any).exceptionName = 'InvalidArgumentException';

    const context: vscode.CodeActionContext = {
      diagnostics: [diagnostic],
      only: undefined,
      triggerKind: vscode.CodeActionTriggerKind.Automatic,
    };

    const actions = provider.provideCodeActions(
      document,
      new vscode.Range(9, 0, 9, 100),
      context,
      {} as vscode.CancellationToken
    );

    assert.ok(actions, 'Should return code actions');
    assert.strictEqual(actions.length, 1, 'Should return one action');

    const edit = actions[0].edit;
    assert.ok(edit, 'Action should have edit');

    // Check that edit exists
    const entries = edit.entries();
    assert.strictEqual(entries.length, 1, 'Should have one edit entry');
  });

  test('Should create docblock if none exists', async () => {
    const provider = new ThrowsCodeActionProvider();

    const content = `<?php

class TestClass {
    public function testMethod() {
        throw new \\RuntimeException('test');
    }
}
`;

    const document = await vscode.workspace.openTextDocument({
      content: content,
      language: 'php',
    });

    const diagnostic = new vscode.Diagnostic(
      new vscode.Range(4, 0, 4, 100),
      'Missing @throws tag for RuntimeException: RuntimeException',
      vscode.DiagnosticSeverity.Warning
    );
    diagnostic.source = 'PHP Exception Inspector';
    diagnostic.code = 'missing_throws';
    (diagnostic as any).exceptionName = 'RuntimeException';

    const context: vscode.CodeActionContext = {
      diagnostics: [diagnostic],
      only: undefined,
      triggerKind: vscode.CodeActionTriggerKind.Automatic,
    };

    const actions = provider.provideCodeActions(
      document,
      new vscode.Range(4, 0, 4, 100),
      context,
      {} as vscode.CancellationToken
    );

    assert.ok(actions, 'Should return code actions');
    assert.ok(actions[0].edit, 'Action should have edit');
  });
});
