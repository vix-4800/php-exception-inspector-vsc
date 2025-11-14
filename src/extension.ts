import * as vscode from 'vscode';
import { InspectorAnalyzer } from './InspectorAnalyzer';
import { ThrowsCodeActionProvider } from './ThrowsCodeActionProvider';

let diagnosticCollection: vscode.DiagnosticCollection;
let analyzer: InspectorAnalyzer;

export function activate(context: vscode.ExtensionContext) {
  diagnosticCollection = vscode.languages.createDiagnosticCollection('phpExceptionInspector');
  context.subscriptions.push(diagnosticCollection);

  analyzer = new InspectorAnalyzer(diagnosticCollection, context.extensionPath);
  context.subscriptions.push(analyzer);

  const codeActionProvider = vscode.languages.registerCodeActionsProvider(
    'php',
    new ThrowsCodeActionProvider(),
    {
      providedCodeActionKinds: ThrowsCodeActionProvider.providedCodeActionKinds,
    }
  );
  context.subscriptions.push(codeActionProvider);

  const analyzeCommand = vscode.commands.registerCommand(
    'phpExceptionInspector.analyzeFile',
    async () => {
      const editor = vscode.window.activeTextEditor;
      if (!editor) {
        vscode.window.showWarningMessage('No active editor found');
        return;
      }

      if (editor.document.languageId !== 'php') {
        vscode.window.showWarningMessage('Current file is not a PHP file');
        return;
      }

      await analyzer.analyzeDocument(editor.document);
      vscode.window.showInformationMessage('PHP Exception Inspector analysis complete');
    }
  );

  context.subscriptions.push(analyzeCommand);

  const onOpenDisposable = vscode.workspace.onDidOpenTextDocument(async (document) => {
    const config = vscode.workspace.getConfiguration('phpExceptionInspector');
    if (config.get<boolean>('analyzeOnOpen') && document.languageId === 'php') {
      await analyzer.analyzeDocument(document);
    }
  });
  context.subscriptions.push(onOpenDisposable);

  const onSaveDisposable = vscode.workspace.onDidSaveTextDocument(async (document) => {
    const config = vscode.workspace.getConfiguration('phpExceptionInspector');
    if (config.get<boolean>('analyzeOnSave') && document.languageId === 'php') {
      await analyzer.analyzeDocument(document);
    }
  });
  context.subscriptions.push(onSaveDisposable);

  vscode.workspace.textDocuments.forEach(async (document) => {
    const config = vscode.workspace.getConfiguration('phpExceptionInspector');
    if (config.get<boolean>('analyzeOnOpen') && document.languageId === 'php') {
      await analyzer.analyzeDocument(document);
    }
  });

  const onCloseDisposable = vscode.workspace.onDidCloseTextDocument((document) => {
    diagnosticCollection.delete(document.uri);
  });
  context.subscriptions.push(onCloseDisposable);
}

export function deactivate() {
  if (diagnosticCollection) {
    diagnosticCollection.clear();
    diagnosticCollection.dispose();
  }
  if (analyzer) {
    analyzer.dispose();
  }
}
