
	function addFileSizeSuffix(filesizeInBytes) {

		filesizeInBytes = parseFloat(filesizeInBytes);

		if (!filesizeInBytes) return false;
		
		b = filesizeInBytes;
		kb = Math.round(b / 1024 * 10) / 10;
		mb = Math.round(kb / 1024 * 10) / 10;
		gb = Math.round(mb / 1024 * 10) / 10;
		tb = Math.round(gb / 1024 * 10) / 10;
		pb = Math.round(tb / 1024 * 10) / 10;
		eb = Math.round(pb / 1024 * 10) / 10;
		zb = Math.round(eb / 1024 * 10) / 10;
		yb = Math.round(zb / 1024 * 10) / 10;

		if (yb > 1) return yb + " YB";
		if (zb > 1) return zb + " ZB";
		if (eb > 1) return eb + " EB";
		if (pb > 1) return pb + " PB";
		if (tb > 1) return tb + " TB";
		if (gb > 1) return gb + " GB";
		if (mb > 1) return mb + " MB";
		if (kb > 1) return kb + " KB";
		if (b > 1) return b + " B";
		
	}
