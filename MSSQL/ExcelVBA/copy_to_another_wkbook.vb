    Sub Foo()
    Dim vFile As Variant
    Dim wbCopyTo As Workbook
    Dim wsCopyTo As Worksheet
    Dim wbCopyFrom As Workbook
    Dim wsCopyFrom As Worksheet

    Set wbCopyTo = ActiveWorkbook
    Set wsCopyTo = ActiveSheet

        '-------------------------------------------------------------
        'Open file with data to be copied
        
        vFile = Application.GetOpenFilename("Excel Files (*.xl*)," & "*.xl*", 1, "Select Excel File", "Open", False)
        
        'If Cancel then Exit
        If TypeName(vFile) = "Boolean" Then
            Exit Sub
        Else
        Set wbCopyFrom = Workbooks.Open(vFile)
        Set wsCopyFrom = wbCopyFrom.Worksheets(1)
        End If
        
        '--------------------------------------------------------------
        'Copy Range
        wsCopyFrom.Range("B6:E12").Copy
        wsCopyTo.Range("B5").PasteSpecial Paste:=xlPasteValues, Operation:=xlNone, SkipBlanks:=False, Transpose:=False
        
        'Close file that was opened
        wbCopyFrom.Close SaveChanges:=False

    End Sub


