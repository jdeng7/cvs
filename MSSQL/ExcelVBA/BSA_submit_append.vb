Rem Attribute VBA_ModuleType=VBAModule
Option VBASupport 1
Sub submit_append()
'
' submit_append Macro
'
'
    Application.MacroOptions Macro:="submit_append", Description:="", _
        ShortcutKey:="x"
    
    Dim Fills As Range
    Dim Filled As Boolean
    
    Filled = True
    
    Worksheets("Form").Activate
    Union(Range(Cells(2, 3), Cells(5, 3)), Cells(8, 3), Cells(10, 3)).Select
    
    For Each cell In Selection
        If IsEmpty(cell) Then
            MsgBox ("Please fill required.")
            Filled = False
            Exit For
        End If
        Next
       
   If Filled = True Then
       
    'identify source sheet
    Sheets("Row").Select
    'select source range
    Range("A2:I2").Select
    'copy from source range
    Selection.Copy
    'select the first cell, optional
    Cells(5, 1).Select
   
   
    'identify target sheet
    Sheets("Report").Select
    'get max row number on column b
    lMaxRows = Cells(Rows.Count, "b").End(xlUp).Row
    'select cell c on last row + 1
    Range("c" & lMaxRows + 1).Select
    'paste value into it, value only, no addition operation (+/-/*//)
    Selection.PasteSpecial Paste:=xlPasteValues, Operation:=xlNone
    'insert TIMESTAMP in cell b of the new row
    Cells(lMaxRows + 1, "b").Value = Date + Time
    'insert TIMESTAMP in cell b of the new row
    Cells(lMaxRows + 1, "b").Value = Date + Time
    'insert "N" into cell a of the new row as deleteion flag
    Cells(lMaxRows + 1, "a").Value = "N"
    'select cell a of the new row
     Cells(lMaxRows + 1, "a").Select
   
    'back to entry sheet
    Sheets("Form").Select
    'select entry area
    Range("C2:C10").Select
    'clear entry area
    Selection.ClearContents
    'select the first entry cell
    Range("C2").Select
    
    End If
   

End Sub



