import React, { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { X, CheckCircle, AlertCircle } from 'lucide-react';
import clsx from 'clsx';

const ToastContext = createContext(null);
let idCounter = 1;

export const ToastProvider = ({ children }) => {
	const [toasts, setToasts] = useState([]);

	const addToast = useCallback(({ type = 'info', title = '', message = '', duration = 5000 }) => {
		const id = idCounter++;
		setToasts((t) => [...t, { id, type, title, message }]);
		if (duration > 0) setTimeout(() => setToasts((t) => t.filter((x) => x.id !== id)), duration);
		return id;
        
	}, []);

	const removeToast = useCallback((id) => setToasts((t) => t.filter((x) => x.id !== id)), []);

	return (
		<ToastContext.Provider value={{ addToast, removeToast }}>
			{children}
			{createPortal(
				<div className="fixed top-6 right-6 z-[11000] flex flex-col space-y-3 w-full max-w-md pointer-events-none">
					{toasts.map((toast) => (
						<div key={toast.id} className="pointer-events-auto">
							<Toast toast={toast} onClose={() => removeToast(toast.id)} />
						</div>
					))}
				</div>,
				document.body
			)}
		</ToastContext.Provider>
	);
};

export const useToast = () => {
	const ctx = useContext(ToastContext);
	if (!ctx) throw new Error('useToast must be used within a ToastProvider');
	return ctx;
};

const iconFor = (type) => {
	if (type === 'success') return <CheckCircle className="h-5 w-5 text-green-500" />;
	if (type === 'error') return <AlertCircle className="h-5 w-5 text-red-500" />;
	return <AlertCircle className="h-5 w-5 text-blue-500" />;
};

const Toast = ({ toast, onClose }) => {
	const { type, title, message } = toast;
	useEffect(() => {}, []);
	return (
		<div className={clsx('bg-white shadow-lg rounded-md p-3 flex items-start space-x-3 border', type === 'error' ? 'border-red-100' : type === 'success' ? 'border-green-100' : 'border-blue-100')} role="status" aria-live="polite">
			<div className="flex-shrink-0">{iconFor(type)}</div>
			<div className="flex-1">
				{title && <div className="text-sm font-semibold text-gray-900">{title}</div>}
				{message && <div className="text-sm text-gray-700 mt-1">{message}</div>}
			</div>
			<button onClick={onClose} className="text-gray-400 hover:text-gray-600 p-1">
				<X className="h-4 w-4" />
			</button>
		</div>
	);
};

export default Toast;
