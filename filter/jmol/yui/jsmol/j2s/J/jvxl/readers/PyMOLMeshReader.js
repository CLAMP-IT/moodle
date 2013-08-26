Clazz.declarePackage ("J.jvxl.readers");
Clazz.load (["J.jvxl.readers.MapFileReader"], "J.jvxl.readers.PyMOLMeshReader", ["java.lang.Float", "J.util.Logger", "$.SB"], function () {
c$ = Clazz.decorateAsClass (function () {
this.map = null;
this.data = null;
this.surfaceList = null;
this.voxelList = null;
this.surfaceName = null;
this.pymolType = 0;
this.isMesh = false;
this.pt = 0;
Clazz.instantialize (this, arguments);
}, J.jvxl.readers, "PyMOLMeshReader", J.jvxl.readers.MapFileReader);
Clazz.makeConstructor (c$, 
function () {
Clazz.superConstructor (this, J.jvxl.readers.PyMOLMeshReader, []);
});
Clazz.overrideMethod (c$, "init2", 
function (sg, brNull) {
this.init2MFR (sg, null);
this.map = sg.getReaderData ();
this.nSurfaces = 1;
this.data = this.map.get (this.params.calculationType);
if (this.data == null) return;
this.pymolType = Clazz.floatToInt (this.getFloat (J.jvxl.readers.PyMOLMeshReader.getList (this.data, 0), 0));
this.isMesh = (this.pymolType == 3);
if (this.isMesh) {
this.surfaceName = this.data.get (this.data.size () - 1);
this.surfaceList = J.jvxl.readers.PyMOLMeshReader.getList (J.jvxl.readers.PyMOLMeshReader.getList (this.map.get (this.surfaceName), 2), 0);
if (this.params.thePlane == null && this.params.cutoffAutomatic) {
this.params.cutoff = this.getFloat (J.jvxl.readers.PyMOLMeshReader.getList (J.jvxl.readers.PyMOLMeshReader.getList (this.data, 2), 0), 8);
this.params.cutoffAutomatic = false;
}} else {
this.surfaceList = J.jvxl.readers.PyMOLMeshReader.getList (J.jvxl.readers.PyMOLMeshReader.getList (this.data, 2), 0);
this.surfaceName = this.data.get (this.data.size () - 1);
}this.voxelList = J.jvxl.readers.PyMOLMeshReader.getList (J.jvxl.readers.PyMOLMeshReader.getList (J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 14), 2), 6);
System.out.println ("Number of grid points = " + this.voxelList.size ());
this.allowSigma = true;
}, "J.jvxl.readers.SurfaceGenerator,java.io.BufferedReader");
c$.getList = $_M(c$, "getList", 
($fz = function (list, i) {
return list.get (i);
}, $fz.isPrivate = true, $fz), "J.util.JmolList,~N");
Clazz.overrideMethod (c$, "readParameters", 
function () {
var t;
this.jvxlFileHeaderBuffer =  new J.util.SB ();
this.jvxlFileHeaderBuffer.append ("PyMOL surface reader\n");
this.jvxlFileHeaderBuffer.append (this.surfaceName + " (" + this.params.calculationType + ")\n");
var s = J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 1);
t = J.jvxl.readers.PyMOLMeshReader.getList (s, 0);
if (t.size () < 3) t = J.jvxl.readers.PyMOLMeshReader.getList (s = J.jvxl.readers.PyMOLMeshReader.getList (s, 0), 0);
this.a = this.getFloat (t, 0);
this.b = this.getFloat (t, 1);
this.c = this.getFloat (t, 2);
t = J.jvxl.readers.PyMOLMeshReader.getList (s, 1);
this.alpha = this.getFloat (t, 0);
this.beta = this.getFloat (t, 1);
this.gamma = this.getFloat (t, 2);
t = J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 7);
this.origin.set (this.getFloat (t, 0), this.getFloat (t, 1), this.getFloat (t, 2));
t = J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 10);
this.na = Clazz.floatToInt (this.getFloat (t, 0));
this.nb = Clazz.floatToInt (this.getFloat (t, 1));
this.nc = Clazz.floatToInt (this.getFloat (t, 2));
t = J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 11);
this.nxyzStart[0] = Clazz.floatToInt (this.getFloat (t, 0));
this.nxyzStart[1] = Clazz.floatToInt (this.getFloat (t, 1));
this.nxyzStart[2] = Clazz.floatToInt (this.getFloat (t, 2));
t = J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 13);
this.nz = Clazz.floatToInt (this.getFloat (t, 0));
this.ny = Clazz.floatToInt (this.getFloat (t, 1));
this.nx = Clazz.floatToInt (this.getFloat (t, 2));
if (this.na < 0 || this.nb < 0 || this.nc < 0) {
this.na = this.nz - 1;
this.nb = this.ny - 1;
this.nc = this.nx - 1;
t = J.jvxl.readers.PyMOLMeshReader.getList (this.surfaceList, 8);
this.a = this.getFloat (t, 0) - this.origin.x;
this.b = this.getFloat (t, 1) - this.origin.y;
this.c = this.getFloat (t, 2) - this.origin.z;
}this.mapc = 3;
this.mapr = 2;
this.maps = 1;
this.getVectorsAndOrigin ();
this.setCutoffAutomatic ();
});
Clazz.overrideMethod (c$, "nextVoxel", 
function () {
return this.getFloat (this.voxelList, this.pt++);
});
$_M(c$, "getFloat", 
($fz = function (list, i) {
return (list.get (i)).floatValue ();
}, $fz.isPrivate = true, $fz), "J.util.JmolList,~N");
Clazz.overrideMethod (c$, "skipData", 
function (nPoints) {
}, "~N");
Clazz.overrideMethod (c$, "setCutoffAutomatic", 
function () {
if (this.params.thePlane != null) return;
if (Float.isNaN (this.params.sigma)) {
if (!this.params.cutoffAutomatic) return;
this.params.cutoff = (this.boundingBox == null ? 3.0 : 1.6);
if (this.dmin != 3.4028235E38) {
if (this.params.cutoff > this.dmax) this.params.cutoff = this.dmax / 4;
}} else {
this.params.cutoff = this.calculateCutoff ();
}J.util.Logger.info ("MapReader: setting cutoff to default value of " + this.params.cutoff + (this.boundingBox == null ? " (no BOUNDBOX parameter)\n" : "\n"));
});
$_M(c$, "calculateCutoff", 
($fz = function () {
var n = this.voxelList.size ();
var sum = 0;
var sum2 = 0;
for (var i = 0; i < n; i++) {
var v = this.getFloat (this.voxelList, i);
sum += v;
sum2 += v * v;
}
var mean = sum / n;
var rmsd = Math.sqrt (sum2 / n);
J.util.Logger.info ("PyMOLMeshReader rmsd=" + rmsd + " mean=" + mean);
return this.params.sigma * rmsd + mean;
}, $fz.isPrivate = true, $fz));
});
