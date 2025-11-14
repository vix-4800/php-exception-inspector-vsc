import * as vscode from 'vscode';

export class ThrowsCodeActionProvider implements vscode.CodeActionProvider {
  public static readonly providedCodeActionKinds = [vscode.CodeActionKind.QuickFix];

  provideCodeActions(
    document: vscode.TextDocument,
    range: vscode.Range | vscode.Selection,
    context: vscode.CodeActionContext,
    // eslint-disable-next-line no-unused-vars
    _token: vscode.CancellationToken
  ): vscode.CodeAction[] | undefined {
    const diagnostics = context.diagnostics.filter(
      (diagnostic) =>
        diagnostic.source === 'PHP Exception Inspector' &&
        (diagnostic.code === 'missing_throws' ||
          diagnostic.code === 'undocumented_throws' ||
          diagnostic.code === 'undeclared_throw' ||
          diagnostic.code === 'undeclared_throw_from_call')
    );

    if (diagnostics.length === 0) {
      return undefined;
    }

    const codeActions: vscode.CodeAction[] = [];

    for (const diagnostic of diagnostics) {
      const exceptionName = (diagnostic as any).exceptionName;
      if (!exceptionName) {
        continue;
      }

      const action = new vscode.CodeAction(
        `Add @throws ${exceptionName}`,
        vscode.CodeActionKind.QuickFix
      );

      action.diagnostics = [diagnostic];
      action.isPreferred = true;

      const edit = this.createThrowsEdit(document, diagnostic, exceptionName);
      if (edit) {
        action.edit = edit;
        codeActions.push(action);
      }
    }

    return codeActions;
  }

  /**
   * Create workspace edit to add @throws tag
   */
  private createThrowsEdit(
    document: vscode.TextDocument,
    diagnostic: vscode.Diagnostic,
    exceptionName: string
  ): vscode.WorkspaceEdit | undefined {
    const methodInfo = this.findMethodDeclaration(document, diagnostic.range.start.line);
    if (!methodInfo) {
      return undefined;
    }

    const edit = new vscode.WorkspaceEdit();
    const docblockInfo = this.findOrCreateDocblockLocation(document, methodInfo);

    if (docblockInfo.existing) {
      const insertPosition = this.findThrowsInsertPosition(
        document,
        docblockInfo.startLine,
        docblockInfo.endLine
      );
      const indent = this.getIndentation(document, methodInfo.declarationLine);
      const throwsLine = `${indent} * @throws ${exceptionName}\n`;
      edit.insert(document.uri, insertPosition, throwsLine);
    } else {
      const indent = this.getIndentation(document, methodInfo.declarationLine);
      const docblock = this.createDocblock(exceptionName, indent);
      edit.insert(document.uri, docblockInfo.position, docblock);
    }

    return edit;
  }

  /**
   * Find method declaration line
   */
  private findMethodDeclaration(
    document: vscode.TextDocument,
    startLine: number
  ): { declarationLine: number; methodName: string } | undefined {
    for (let line = startLine; line >= Math.max(0, startLine - 50); line--) {
      const text = document.lineAt(line).text;
      const match = text.match(/^\s*(public|protected|private|static|\s)*function\s+(\w+)/);
      if (match) {
        return {
          declarationLine: line,
          methodName: match[2],
        };
      }
    }
    return undefined;
  }

  /**
   * Find existing docblock or determine where to create one
   */
  private findOrCreateDocblockLocation(
    document: vscode.TextDocument,
    methodInfo: { declarationLine: number }
  ): {
    existing: boolean;
    startLine: number;
    endLine: number;
    position: vscode.Position;
  } {
    let checkLine = methodInfo.declarationLine - 1;

    while (checkLine >= 0 && document.lineAt(checkLine).text.trim() === '') {
      checkLine--;
    }

    if (checkLine >= 0) {
      const text = document.lineAt(checkLine).text.trim();
      if (text === '*/') {
        let startLine = checkLine;
        while (startLine > 0) {
          const lineText = document.lineAt(startLine).text.trim();
          if (lineText.startsWith('/**')) {
            return {
              existing: true,
              startLine: startLine,
              endLine: checkLine,
              position: new vscode.Position(checkLine, 0),
            };
          }
          startLine--;
        }
      }
    }

    return {
      existing: false,
      startLine: -1,
      endLine: -1,
      position: new vscode.Position(methodInfo.declarationLine, 0),
    };
  }

  /**
   * Find the correct position to insert @throws within existing docblock
   */
  private findThrowsInsertPosition(
    document: vscode.TextDocument,
    startLine: number,
    endLine: number
  ): vscode.Position {
    let lastParamLine = -1;
    let lastReturnLine = -1;
    let firstThrowsLine = -1;

    for (let line = startLine + 1; line < endLine; line++) {
      const text = document.lineAt(line).text;
      if (text.includes('@param')) {
        lastParamLine = line;
      } else if (text.includes('@return')) {
        lastReturnLine = line;
      } else if (text.includes('@throws')) {
        if (firstThrowsLine === -1) {
          firstThrowsLine = line;
        }
      }
    }

    if (firstThrowsLine !== -1) {
      let lastThrowsLine = firstThrowsLine;
      for (let line = firstThrowsLine + 1; line < endLine; line++) {
        const text = document.lineAt(line).text;
        if (text.includes('@throws')) {
          lastThrowsLine = line;
        } else if (text.trim().startsWith('*') && text.includes('@')) {
          break;
        }
      }
      return new vscode.Position(lastThrowsLine + 1, 0);
    }

    if (lastReturnLine !== -1) {
      return new vscode.Position(lastReturnLine + 1, 0);
    }

    if (lastParamLine !== -1) {
      return new vscode.Position(lastParamLine + 1, 0);
    }

    return new vscode.Position(endLine, 0);
  }

  /**
   * Get indentation of a line
   */
  private getIndentation(document: vscode.TextDocument, line: number): string {
    const text = document.lineAt(line).text;
    const match = text.match(/^(\s*)/);
    return match ? match[1] : '';
  }

  /**
   * Create a new docblock with @throws tag
   */
  private createDocblock(exceptionName: string, indent: string): string {
    return `${indent}/**\n${indent} * @throws ${exceptionName}\n${indent} */\n`;
  }
}
