' Replaces all cells in the format #-# (or ##-## and so forth)
' with their integer representation.
' For example, 10-15 will be replaced with 6.
Sub RangeToValue()
    Dim rngCell As Range
    Dim allMatches As Object
    Dim strPattern As String: strPattern = "[0-9]*"
    Dim regEx As New RegExp
    Dim i As Object
    Dim j As Object
    Dim ii As Integer
    Dim jj As Integer
   
    ' ***BE SURE TO SET THIS***
    Set rngCell = Range("G219:O351")

    For Each cell In rngCell.Cells
        If cell.Value = 0 Then ' do nothing, write for formatting sake
            cell.Value = 0
        ElseIf cell.Value = 1 Then ' same
            cell.Value = 1
        ElseIf InStr(cell.Value, "-") = 0 Then ' does not contain hyphen, so change it to 1 (Ex: 20)
            cell.Value = 1
        Else ' Cell contains hyphen and needs to be calculated
            ' Set up regEx object
            With regEx
                .Global = True
                .MultiLine = False
                .IgnoreCase = False
                .Pattern = strPattern
            End With
            Set allMatches = regEx.Execute(cell.Value) ' should always return 2 matches
            Set i = allMatches.Item(0)
            Set j = allMatches.Item(2)
            ii = CInt(i)
            jj = CInt(j)
            computedValue = jj - ii + 1
            ' Debug.Print ii, jj, computedValue
            cell.Value = computedValue
            ' Debug.Print cell.Address, cell.Value
        End If
    Next cell

End Sub

Sub RemoveEmptyRows()
    'If nothing in first column but something elsewhere in the row, delete the row
    ' While loop only used because it doesn't delete everything in one pass
    ' ***BE SURE TO SET THIS***
    Set colrng = Range("A4:A600")
    Dim i As Integer
    i = 0
    While i < 10
        For Each c In colrng
            If IsEmpty(c) Then c.EntireRow.Delete
        Next
        i = i + 1
    Wend
End Sub
