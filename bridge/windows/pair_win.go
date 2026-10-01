//go:build windows

package main

import (
	"runtime"
	"sync"
	"syscall"
	"unsafe"
)

const (
	wmSetTextPair = 0x000C
	mbOK          = 0x00000000
	mbIconError   = 0x00000010
)

var (
	pSetWindowTextWPair = user32Notice.NewProc("SetWindowTextW")
	pMessageBoxWPair    = user32Notice.NewProc("MessageBoxW")
	pairWindows         sync.Map
)

type pairingWindow struct {
	hwnd         uintptr
	codeHwnd     uintptr
	statusHwnd   uintptr
	closed       chan struct{}
	closeOnce    sync.Once
	programClose bool
	mu           sync.Mutex
}

func pairWndProc(hwnd uintptr, msg uint32, wParam, lParam uintptr) uintptr {
	switch msg {
	case wmClose:
		pDestroyWindow.Call(hwnd)
		return 0
	case wmDestroy:
		if v, ok := pairWindows.Load(hwnd); ok {
			w := v.(*pairingWindow)
			w.closeOnce.Do(func() { close(w.closed) })
			pairWindows.Delete(hwnd)
		}
		pPostQuitMessage.Call(0)
		return 0
	}
	r, _, _ := pDefWindowProcW.Call(hwnd, uintptr(msg), wParam, lParam)
	return r
}

var pairWndProcPtr = syscall.NewCallback(pairWndProc)

func newPairingWindow(code string) (*pairingWindow, error) {
	w := &pairingWindow{closed: make(chan struct{})}
	ready := make(chan error, 1)
	go func() {
		runtime.LockOSThread()
		defer runtime.UnlockOSThread()

		className, _ := syscall.UTF16PtrFromString("HSVoucherBridgePairingWindowV016")
		title, _ := syscall.UTF16PtrFromString("HSBridge V005")
		staticClass, _ := syscall.UTF16PtrFromString("STATIC")
		heading, _ := syscall.UTF16PtrFromString("Parovaci kod")
		instruction, _ := syscall.UTF16PtrFromString("Zadejte tento kod na webu v Vouchery -> Propojeni.")
		status, _ := syscall.UTF16PtrFromString("Cekam na propojeni...")
		codeText, _ := syscall.UTF16PtrFromString(code)
		hInst, _, _ := pGetModuleHandleW.Call(0)

		wc := noticeWndClassEx{
			CbSize:        uint32(unsafe.Sizeof(noticeWndClassEx{})),
			LpfnWndProc:   pairWndProcPtr,
			HInstance:     hInst,
			HbrBackground: uintptr(colorWindow + 1),
			LpszClassName: className,
		}
		pRegisterClassExW.Call(uintptr(unsafe.Pointer(&wc)))

		const width, height = 520, 285
		sw, _, _ := pGetSystemMetrics.Call(0)
		sh, _, _ := pGetSystemMetrics.Call(1)
		x := (int(sw) - width) / 2
		y := (int(sh) - height) / 3
		if x < 0 {
			x = 0
		}
		if y < 0 {
			y = 0
		}

		hwnd, _, createErr := pCreateWindowExW.Call(
			uintptr(wsExTopmost|wsExToolWin),
			uintptr(unsafe.Pointer(className)),
			uintptr(unsafe.Pointer(title)),
			uintptr(wsPopup|wsCaption|wsVisible),
			uintptr(x), uintptr(y), uintptr(width), uintptr(height),
			0, 0, hInst, 0,
		)
		if hwnd == 0 {
			ready <- createErr
			return
		}
		w.hwnd = hwnd
		pairWindows.Store(hwnd, w)

		createStatic := func(text *uint16, top, h int, center bool) uintptr {
			style := uintptr(wsChild | wsVisible)
			if center {
				style |= ssCenter
			}
			child, _, _ := pCreateWindowExW.Call(
				0,
				uintptr(unsafe.Pointer(staticClass)),
				uintptr(unsafe.Pointer(text)),
				style,
				20, uintptr(top), width-40, uintptr(h),
				hwnd, 0, hInst, 0,
			)
			if child != 0 {
				font, _, _ := pGetStockObject.Call(defaultGuiFont)
				if font != 0 {
					pSendMessageW.Call(child, wmSetFont, font, 1)
				}
			}
			return child
		}

		createStatic(heading, 28, 28, true)
		w.codeHwnd = createStatic(codeText, 72, 48, true)
		createStatic(instruction, 138, 28, true)
		w.statusHwnd = createStatic(status, 185, 32, true)

		pShowWindow.Call(hwnd, 5)
		pUpdateWindow.Call(hwnd)
		logf("GUI STATUS WINDOW OPENED hwnd=%d", hwnd)
		ready <- nil

		var m noticeMsg
		for {
			r, _, _ := pGetMessageW.Call(uintptr(unsafe.Pointer(&m)), 0, 0, 0)
			if int32(r) <= 0 {
				break
			}
			pTranslateMessage.Call(uintptr(unsafe.Pointer(&m)))
			pDispatchMessageW.Call(uintptr(unsafe.Pointer(&m)))
		}
	}()
	if err := <-ready; err != nil {
		return nil, err
	}
	return w, nil
}

func (w *pairingWindow) setStatus(s string) {
	if w == nil || w.statusHwnd == 0 {
		return
	}
	p, _ := syscall.UTF16PtrFromString(s)
	pSetWindowTextWPair.Call(w.statusHwnd, uintptr(unsafe.Pointer(p)))
}

func (w *pairingWindow) setCode(s string) {
	if w == nil || w.codeHwnd == 0 {
		return
	}
	p, _ := syscall.UTF16PtrFromString(s)
	pSetWindowTextWPair.Call(w.codeHwnd, uintptr(unsafe.Pointer(p)))
}

func (w *pairingWindow) close() {
	if w == nil || w.hwnd == 0 {
		return
	}
	w.mu.Lock()
	w.programClose = true
	w.mu.Unlock()
	pPostMessageW.Call(w.hwnd, wmClose, 0, 0)
}

func (w *pairingWindow) wasProgramClosed() bool {
	if w == nil {
		return false
	}
	w.mu.Lock()
	defer w.mu.Unlock()
	return w.programClose
}

func showMessageBox(title, message string) {
	tp, _ := syscall.UTF16PtrFromString(title)
	mp, _ := syscall.UTF16PtrFromString(message)
	pMessageBoxWPair.Call(0, uintptr(unsafe.Pointer(mp)), uintptr(unsafe.Pointer(tp)), mbOK|mbIconError)
}
