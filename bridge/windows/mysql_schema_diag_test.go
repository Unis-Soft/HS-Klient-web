//go:build windows

package main

import "testing"

func TestCompactSQLForLog(t *testing.T) {
	in := "CREATE TABLE x (\n  id int,\n  name varchar(10)\n)"
	got := compactSQLForLog(in)
	want := "CREATE TABLE x ( id int, name varchar(10) )"
	if got != want {
		t.Fatalf("compactSQLForLog()=%q want %q", got, want)
	}
}

func TestMySQLQuoteIdentifier(t *testing.T) {
	got := mysqlQuoteIdentifier("program`values")
	want := "`program``values`"
	if got != want {
		t.Fatalf("mysqlQuoteIdentifier()=%q want %q", got, want)
	}
}
